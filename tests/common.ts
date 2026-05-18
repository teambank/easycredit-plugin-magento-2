import { test, expect } from "@playwright/test";
import { delay, randomize, doWithRetry } from "./utils";
import { PaymentTypes } from "./types";

export const goToProduct = async (page, sku = 'regular-product') => {
  await test.step(`Go to product (sku: ${sku}}`, async () => {
    await page.goto(`index.php/${sku}.html`);
  });
};

export const addCurrentProductToCart = async (page) => {
    const addToCartResponse = page.waitForResponse(
      (response) =>
        response.request().method() === "POST" &&
        /checkout\/cart\/add/.test(response.url())
    );
    await page
      .getByRole("button", { name: "In den Warenkorb" })
      .first()
      .click();
    await addToCartResponse;

    await expect(page.locator(".page.messages")).toContainText(
      /Sie haben .+? zu Ihrem Warenkorb hinzugefügt./
    );
};

const expressPaymentLabel: Record<PaymentTypes, RegExp> = {
  [PaymentTypes.INSTALLMENT]: /in Raten zahlen/i,
  [PaymentTypes.BILL]: /auf Rechnung/i,
};

/** Clicks an option inside the easycredit-express-button web component (not a page-level link). */
export const clickExpressCheckout = async (
  page,
  paymentType: PaymentTypes
) => {
  const label = expressPaymentLabel[paymentType];

  await test.step(`Express checkout (${paymentType})`, async () => {
    const express = page.locator("easycredit-express-button").first();
    await expect(express).toBeVisible({ timeout: 30_000 });

    await doWithRetry(async () => {
      const option = express.getByText(label);
      await expect(option.first()).toBeVisible({ timeout: 5_000 });
      await option.first().click();
    });
  });
};

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

    if (switchPaymentType) {
      const switchButton = page
        .locator(".paymentoptions")
        .getByText(
          paymentType === PaymentTypes.INSTALLMENT ? "Rechnung" : "Ratenkauf"
        );
      await expect(switchButton).toBeVisible();
      await switchButton.click({ force: true });
    }

    await page.getByRole("button", { name: "Weiter" }).click();

    // Fill mobile number for sms tan
    await page
      .locator("#mobilfunknummer")
      .getByRole("textbox")
      .fill("1703404848");

    await doWithRetry(async () => {
      await page.getByRole("button", { name: "SMS-TAN senden" }).click();
      await delay(500);
      const mtanInput = page.locator("#mTAN").getByRole("textbox");
      const canFillMtan =
        (await mtanInput.isVisible()) && (await mtanInput.isEditable());
      if (!canFillMtan) {
        throw new Error("mTAN input is not fillable yet");
      }
    });

    // Enter the code from the SMS (anything works)
    await page.locator("#mTAN").getByRole("textbox").fill("123456");

    await doWithRetry(async () => {
      await page.getByRole("button", { name: "Zur Dateneingabe" }).click();
    });

    if (express) {
      await page.locator("#firstName").fill(randomize("Ralf"));
      await page.locator("#lastName").fill("Ratenkauf");
    }

    await page.locator("#dateOfBirth").getByRole("textbox").fill("05.04.1972");

    if (express) {
      await page
        .locator("#email")
        .getByRole("textbox")
        .fill("ralf.ratenkauf@teambank.de");
    }

    await page
      .locator("app-ratenkauf-iban-input-dumb")
      .getByRole("textbox")
      .fill("DE12500105170648489890");

    if (express) {
      await page.locator("#streetAndNumber").fill("Beuthener Str. 25");
      await page.locator("#postalCode").fill("90402");
      await page.locator("#city").fill("Nürnberg");
    }

    await doWithRetry(async () => {
      await page.locator("#sepamandat tbk-svg-icon").click({ force: true });
      await delay(500);
      const isChecked = await page.locator("#agreeSepa").isChecked();
      if (!isChecked) {
        throw new Error("SEPA checkbox was not checked");
      }
    });

    await page.locator("#next-btn").click();

    await delay(500);
    await doWithRetry(async () => {
      await page.getByRole("button", { name: "Zahlung übernehmen" }).click();
    });
  });
};