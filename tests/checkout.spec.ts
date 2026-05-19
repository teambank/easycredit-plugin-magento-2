import { test, expect } from '@playwright/test';
import { randomize, takeScreenshot, scaleDown, delay } from "./utils";
import {
  goToProduct,
  addCurrentProductToCart,
  clickExpressCheckout,
  goThroughPaymentPage,
  confirmOrder,
  goToEasyCreditReview,
  submitEasyCreditPlaceOrder,
} from "./common";
import { PaymentTypes } from "./types";

test.beforeEach(scaleDown)
test.afterEach(takeScreenshot);

const fillCheckout = async (page) => {
    var randomLetters = "";
    for (let i = 0; i < 3; i++) {
      randomLetters += String.fromCharCode(97 + Math.floor(Math.random() * 26));
    }
    await page
      .getByRole("textbox", { name: "Vorname" })
      .fill(randomize("Ralf"));
    await page.getByRole("textbox", { name: "Nachname" }).fill("Ratenkauf");

    await page.getByRole("textbox", { name: "E-Mail" }).fill("test@email.com");

    await page.getByLabel("Straße: Line 1").fill("Beuthener Str. 25");

    await page.getByLabel("Land", { exact: true }).selectOption("DE");
    await page.locator('select[name="region_id"]').selectOption("81");

    await page.getByRole("textbox", { name: "Stadt" }).fill("Nürnberg");
    await page.getByRole("textbox", { name: "PLZ" }).fill("90402");
    await page.getByLabel("Telefonnummer").fill("01703404848");

    /* select shipping method */
    await page.getByLabel("Fixed").click();
    await page.getByRole("button", { name: "Weiter" }).click();

}

test.describe("Go through standard @installment", () => {
  test("standardCheckout", async ({ page }) => {
    await goToProduct(page);
    await addCurrentProductToCart(page);

    await page.goto("index.php/checkout/");

    await fillCheckout(page);

    /* Confirm Page */
    await page
      .locator("easycredit-checkout-label[payment-type=INSTALLMENT]")
      .click();
    await page.getByRole("button", { name: "Weiter zu easyCredit-Ratenkauf" }).click();

    await goThroughPaymentPage({
      page: page,
      paymentType: PaymentTypes.INSTALLMENT,
    });
    await confirmOrder({
      page: page,
      paymentType: PaymentTypes.INSTALLMENT,
    });
  });
});

test.describe("Go through standard @bill", () => {
  test("standardCheckout", async ({ page }) => {
    await goToProduct(page);
    await addCurrentProductToCart(page);

    await page.goto("index.php/checkout/");

    await fillCheckout(page);

    /* Confirm Page */
    await page.locator("easycredit-checkout-label[payment-type=BILL]").click();
    await page
      .locator("easycredit-checkout")
      .getByRole("button", { name: "auf Rechnung zahlen" })
      .click();

    await goThroughPaymentPage({
      page: page,
      paymentType: PaymentTypes.BILL,
    });
    await confirmOrder({
      page: page,
      paymentType: PaymentTypes.BILL,
    });
  });
});

test.describe("Go through @express @installment", () => {
  test("expressCheckout", async ({ page }) => {
    await goToProduct(page);

    await clickExpressCheckout(page, PaymentTypes.INSTALLMENT);
    await page.getByText("Akzeptieren", { exact: true }).click();

    await goThroughPaymentPage({
      page: page,
      paymentType: PaymentTypes.INSTALLMENT,
      express: true,
    });
    await confirmOrder({
      page: page,
      paymentType: PaymentTypes.INSTALLMENT,
    });
  });
});

test.describe("Go through @express @bill", () => {
  test("expressCheckout", async ({ page }) => {
    await goToProduct(page);

    await clickExpressCheckout(page, PaymentTypes.BILL);
    await page.getByText("Akzeptieren", { exact: true }).click();

    await goThroughPaymentPage({
      page: page,
      paymentType: PaymentTypes.BILL,
      express: true,
    });
    await confirmOrder({
      page: page,
      paymentType: PaymentTypes.BILL,
    });
  });
});

test.describe("company should not be able to  @bill @installment", () => {
  test("companyBlocked", async ({ page }) => {
    await goToProduct(page);
    await addCurrentProductToCart(page);

    await page.goto("index.php/checkout/");
    await page.getByRole("textbox", { name: "Firma" }).fill("Firma");
    await fillCheckout(page);

    /* Confirm Page */
    for (let paymentType of [PaymentTypes.BILL, PaymentTypes.INSTALLMENT]) {
      await page.locator(`easycredit-checkout-label[payment-type=${paymentType}]`).click();
      await expect(
        await page.locator(`easycredit-checkout[payment-type=${paymentType}]`)
      ).toContainText(
        "Die Zahlung mit easyCredit ist nur für Privatpersonen möglich."
      );
    }
  });
});

test.describe("amount should not be changable afterwards @bill @installment", () => {
  test("amountNotChangable", async ({ page }) => {
    await goToProduct(page);

    await clickExpressCheckout(page, PaymentTypes.INSTALLMENT);
    await page.getByText("Akzeptieren", { exact: true }).click();

    await goThroughPaymentPage({
      page: page,
      paymentType: PaymentTypes.INSTALLMENT,
      express: true
    });
    await expect(
      page.getByRole('heading', {name: "Bestellung überprüfen"})
    ).toBeVisible();
    
    await page.goto("index.php/checkout/cart/");

    await page.getByRole("spinbutton", { name: "Menge" }).first().fill("2");
    const cartUpdateResponse = page.waitForResponse(
      (response) =>
        response.request().method() === "POST" &&
        /checkout\/cart\/updatePost/.test(response.url())
    );
    await page.getByRole("button", { name: "Warenkorb aktualisieren" }).click();
    await cartUpdateResponse;
    await page.waitForLoadState("networkidle");

    await goToEasyCreditReview(page);
    await submitEasyCreditPlaceOrder(page);

    await expect(page.locator(".page.messages")).toContainText(
      /Unable to finish easyCredit Checkout\. Validation failed\.|Unable to finish easyCredit Checkout\. Please restart payment process\.|Unable to initialize easyCredit Checkout review\.|Der Einkauf mit easyCredit konnte nicht abgeschlossen werden\./
    );
  });
});

test.describe("product below amount constraint should not be buyable @bill @installment", () => {
  test("productBelowAmountConstraint", async ({ page }) => {
    await goToProduct(page, "below-50");
    await addCurrentProductToCart(page);

    await page.goto("index.php/checkout/");
    await fillCheckout(page);

    /* Confirm Page */
    for (let paymentType of [PaymentTypes.BILL, PaymentTypes.INSTALLMENT]) {
      await page
        .locator(`easycredit-checkout-label[payment-type=${paymentType}]`)
        .click();
      await expect(
        await page.locator(`easycredit-checkout[payment-type=${paymentType}]`)
      ).toContainText("liegt außerhalb der zulässigen Beträge");
    }
  });
});

test.describe("product above amount constraint should not be buyable  @bill @installment", () => {
  test("productAboveAmountConstraint", async ({ page }) => {
    await goToProduct(page, "above-10000");
    await addCurrentProductToCart(page);

    await page.goto("index.php/checkout/");
    await fillCheckout(page);

    /* Confirm Page */
    for (let paymentType of [PaymentTypes.BILL, PaymentTypes.INSTALLMENT]) {
      await page
        .locator(`easycredit-checkout-label[payment-type=${paymentType}]`)
        .click();
      await expect(
        await page.locator(`easycredit-checkout[payment-type=${paymentType}]`)
      ).toContainText("liegt außerhalb der zulässigen Beträge");
    }
  });
});
