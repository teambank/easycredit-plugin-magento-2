import { test, expect } from "@playwright/test";
import { delay, randomize } from "./utils";
import { PaymentTypes } from "./types";

export const goToProduct = async (page, num = 0) => {
  await test.step(`Go to product (num: ${num}}`, async () => {
    await page.goto("index.php/product.html");
  });
};

export const addCurrentProductToCart = async (page) => {
    await page
      .getByRole("button", { name: "In den Warenkorb" })
      .first()
      .click();
    await expect(
      page.getByText("Sie haben Product zu Ihrem Warenkorb hinzugefügt.")
    ).toBeVisible();
}

export const confirmOrder = async ({
  page,
  paymentType,
}: {
  page: any;
  paymentType: PaymentTypes;
}) => {
  await test.step(`Confirm order`, async () => {
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
}: {
  page: any;
  paymentType: PaymentTypes;
  express?: boolean;
}) => {
  await test.step(`easyCredit-Ratenkauf Payment`, async () => {
    await page.getByTestId("uc-deny-all-button").click();

	await expect(
      page.getByText(
        paymentType === PaymentTypes.INSTALLMENT
          ? "Ihre monatliche Wunschrate"
          : "Rechnung"
      )
    ).toBeVisible();

    await page.getByRole("button", { name: "Weiter zur Dateneingabe" }).click();

    if (express) {
      await page.locator("#vorname").fill(randomize("Ralf"));
      await page.locator("#nachname").fill("Ratenkauf");
    }

    await page.locator("#geburtsdatum").fill("05.04.1972");

    if (express) {
      await page.locator("#email").fill("ralf.ratenkauf@teambank.de");
    }
    await page.locator("#mobilfunknummer").fill("015112345678");
    await page.locator("#iban").fill("DE12500105170648489890");

    if (express) {
      await page.locator("#strasseHausNr").fill("Beuthener Str. 25");
      await page.locator("#plz").fill("90402");
      await page.locator("#ort").fill("Nürnberg");
    }

    await page.getByText("Allen zustimmen").click();

    await delay(500);
    await page.getByRole("button", { name: "Ratenwunsch prüfen" }).click();

    await delay(500);
    await page.getByRole("button", { name: "Ratenwunsch übernehmen" }).click();
  });
};
