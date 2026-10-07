import { test, expect } from "@playwright/test";
import { doWithRetry } from "./utils";
import { PaymentTypes } from "./types";
import { createEasyCreditPayment } from "easycredit-playwright-payment";

export const { goThroughPaymentPage } = createEasyCreditPayment({
  returnUrlPattern: /easycredit\/checkout\/review/i,
});

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

    await page.getByRole("button", { name: "Jetzt kaufen" }).click();

    await expect(
      page.getByText("Vielen Dank für Ihre Bestellung!")
    ).toBeVisible();
  });
};

/** After cart changes easyCredit may redirect review → cart; waitUntil commit avoids Playwright errors. */
export const goToEasyCreditReview = async (page) => {
  await test.step("Open easyCredit review", async () => {
    await page.goto("index.php/easycredit/checkout/review", {
      waitUntil: "commit",
    });
    await page.waitForLoadState("domcontentloaded");
  });
};

/** Submit place order from review, or hit placeorder controller when review redirected to cart. */
export const submitEasyCreditPlaceOrder = async (page) => {
  await test.step("Submit easyCredit place order", async () => {
    if (/easycredit\/checkout\/review/.test(page.url())) {
      await page.getByRole("button", { name: "Jetzt kaufen" }).click();
      await page.waitForLoadState("domcontentloaded");
      return;
    }

    await page.goto("index.php/easycredit/checkout/placeorder", {
      waitUntil: "commit",
    });
    await page.waitForLoadState("domcontentloaded");
  });
};

