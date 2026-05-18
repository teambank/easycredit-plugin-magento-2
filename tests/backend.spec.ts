import { test, expect } from "@playwright/test";
import { takeScreenshot, scaleDown } from "./utils";

test.beforeEach(scaleDown);
test.afterEach(takeScreenshot);

/* disable temporarly, "Zahlungsarten" menu item cannot be found in any system
test.describe("Check settings", () => {
  test("Check settings", async ({ page }) => {
    await page.goto("/admin/");
    await page.getByPlaceholder("Benutzername").fill("admin");
    await page.getByPlaceholder("Passwort").fill("admin12345678!");
    await page.getByRole("button", { name: "Anmelden" }).click();

    await page.getByRole("link", { name: "Shops" }).click();
    await page.getByRole("link", { name: "Konfiguration" }).click();
    await page
      .getByRole("tab", { name: "Verkäufe" })
      .getByRole("strong")
      .click();

    await page.evaluate(() => {
      window.scrollTo(0, document.body.scrollHeight);
    });
    await page.getByRole("tab", { name: "Zahlungsarten" }).click();
    
    //await page.getByRole("link", {name: "Additional payment solutions"}).click();

    await page.locator('[data-test-id="easycredit-config-button"]').click();

    await page.getByRole("link", { name: "Zugangsdaten überprüfen" }).click();
    await page.getByText("Die Zugangsdaten sind gültig.").click();
  });
});
*/
