import { test, expect } from "@playwright/test";
import { delay, randomize, clickWithRetry } from "./utils";
import { PaymentTypes } from "./types";

export const goToProduct = async (page, sku = 'regular-product') => {
  await test.step(`Go to product (sku: ${sku}}`, async () => {
    await page.goto(`index.php/${sku}.html`);
  });
};

export const addCurrentProductToCart = async (page) => {
    await page
      .getByRole("button", { name: "In den Warenkorb" })
      .first()
      .click();
    await page.waitForResponse(/checkout\/cart\/add/);

    await expect(page.locator(".page.messages")).toContainText(
      /Sie haben .+? zu Ihrem Warenkorb hinzugefügt./
    );
}

export const confirmOrder = async ({
  page,
  paymentType,
}: {
  page: any;
  paymentType: PaymentTypes;
}) => {
  await test.step(`Confirm order`, async () => {

		await expect(page.locator("easycredit-checkout-label")).toContainText(
      paymentType === PaymentTypes.INSTALLMENT ? "Ratenkauf" : "Rechnung"
    );

    if (paymentType === PaymentTypes.INSTALLMENT) {
      await expect
        .soft(page.locator(".opc-block-summary"))
        .toContainText("Zinsen für Ratenzahlung");
    } else {
      await expect
        .soft(page.locator(".opc-block-summary"))
        .not.toContainText("Zinsen für Ratenzahlung");
    }

    /* Confirm Page */
    //await page.getByLabel('Please accept the terms').check();
    await page.getByRole("button", { name: "Jetzt kaufen" }).click();

    /* Success Page */
    await expect(
      page.getByText("Vielen Dank für Ihre Bestellung!")
    ).toBeVisible();
  });
};

export const goThroughPaymentPage = async ({
  page,
  paymentType,
  express = false,
  switchPaymentType = false,
}: {
  page: any;
  paymentType: PaymentTypes;
  express?: boolean;
  switchPaymentType?: boolean;
}) => {
  await test.step(`easyCredit Payment (${paymentType})`, async () => {
    await page.getByTestId("uc-deny-all-button").click();

    await expect(
      page.getByRole("heading", {
        name:
          paymentType === PaymentTypes.INSTALLMENT
            ? "Monatliche Wunschrate"
            : "Ihre Bezahloptionen",
      })
    ).toBeVisible();

    if (switchPaymentType) {
      await page
        .locator(".paymentoptions")
        .getByText(
          paymentType === PaymentTypes.INSTALLMENT ? "Rechnung" : "Ratenkauf"
        )
        .click();
    }

    await page.getByRole("button", { name: "Weiter zur Dateneingabe" }).click();

    if (express) {
      await page.locator("#firstName").fill(randomize("Ralf"));
      await page.locator("#lastName").fill("Ratenkauf");
    }

    await page.locator("#dateOfBirth").fill("05.04.1972");

    if (express) {
      await page
        .locator("#email")
        .getByRole("textbox")
        .fill("ralf.ratenkauf@teambank.de");
    }

    await page
      .locator("#mobilfunknummer")
      .getByRole("textbox")
      .fill("1703404848");
    await page
      .locator("app-ratenkauf-iban-input-dumb")
      .getByRole("textbox")
      .fill("DE12500105170648489890");

    if (express) {
      await page.locator("#streetAndNumber").fill("Beuthener Str. 25");
      await page.locator("#postalCode").fill("90402");
      await page.locator("#city").fill("Nürnberg");
    }

    await page.locator("#agreeSepa").click();

    await delay(500);

    await clickWithRetry(
      page.getByRole("button", { name: "Zahlungswunsch prüfen" })
    );

    await delay(500);
    await page
      .getByRole("button", { name: "Zahlungswunsch übernehmen" })
      .click();
  });
};