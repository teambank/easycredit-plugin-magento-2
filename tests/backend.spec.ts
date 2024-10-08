import { test, expect } from "@playwright/test";
test("Check settings", async ({ page }) => {
  await page.goto("/admin/");
  await page.getByPlaceholder("Benutzername").fill("admin");
  await page.getByPlaceholder("Passwort").fill("admin1234578!");
  await page.getByRole("button", { name: "Anmelden" }).click();

  await page.getByRole("link", { name: "Shops" }).click();
  await page.getByRole("link", { name: "Konfiguration" }).click();
  await page.getByRole("tab", { name: "Verkäufe" }).getByRole("strong").click();
  await page.getByRole("tab", { name: "Zahlungsarten" }).click();

  await page.locator('[data-test-id="easycredit-config-button"]').click();

  await page.getByRole("link", { name: "Zugangsdaten überprüfen" }).click();
  await page.getByText("Die Zugangsdaten sind gültig.").click();
});
