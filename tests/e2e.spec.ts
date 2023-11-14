import { test, expect } from '@playwright/test';

function delay(time) {
  return new Promise(function(resolve) {
      setTimeout(resolve, time)
  });
}

test.beforeEach(async ({page}, testInfo) => {
  await page.evaluate(() => {
    document.body.style.transform = 'scale(0.75)'
  })
})

test.beforeAll(async ({ request}, testInfo) => {
  var headers = {
    'Content-Type': 'application/json',
    Accept: 'application/json'
  }

  var response = await request.post('/rest/default/V1/integration/admin/token', {
    headers: headers,
    data: {
      "username": "admin",
      "password": "admin1234578!"
    }
  });
  const authorization = await response.json()
  headers['Authorization'] = 'Bearer ' + authorization;

  response = await request.post('/rest/V1/categories', {
    headers: headers,
    data: {
      "category": {
        "parent_id": 2,
        "name": "Products",
        "is_active": 1
      }
    }
  })
  var categoryData = await response.json()
  const categoryId = categoryData.id

  console.log({
    categoryId: categoryId
  })

  var response = await request.post('/rest/V1/products', {
    headers: headers,
    data: {
      "product": {
        "sku": "product",
        "name": "Product",
        "attribute_set_id": 4,
        "price": 201,
        "status": 1,
        "visibility": 4,
        "type_id": "simple",
        "weight": "1",
        "extension_attributes": {
          "category_links":[{
            "position": 0,
            "category_id": categoryId,
            "extension_attributes": null
          }],
          "stock_item": {
            "qty": "10",
            "is_in_stock": true
          }
        }
      }
    }
  })

  var response = await request.post('/rest/V1/products', {
    headers: headers,
    data: {
      "product": {
        "sku": "cheap-product",
        "name": "Cheap Product",
        "attribute_set_id": 4,
        "price": 21,
        "status": 1,
        "visibility": 4,
        "type_id": "simple",
        "weight": "1",
        "extension_attributes": {
          "category_links":[{
            "position": 0,
            "category_id": categoryId,
            "extension_attributes": null
          }],
          "stock_item": {
            "qty": "10",
            "is_in_stock": true
          }
        }
      }
    }
  })
})
test.afterEach(async ({ page }, testInfo) => {
  if (testInfo.status !== testInfo.expectedStatus) {
    // Get a unique place for the screenshot.
    const screenshotPath = testInfo.outputPath(`failure.png`);
    // Add it to the report.
    testInfo.attachments.push({ name: 'screenshot', path: screenshotPath, contentType: 'image/png' });
    // Take the screenshot itself.
    await page.screenshot({ path: screenshotPath, timeout: 5000 });
  }
});

const randomize = (name, num = 3) => {
  for (let i = 0; i < num; i++) {
    name += String.fromCharCode(97+Math.floor(Math.random() * 26));
  }
  return name
}

const goThroughPaymentPage = async (page, express: boolean = false) => {
  await test.step(`easyCredit-Ratenkauf Payment`, async() => {
    await page.getByTestId('uc-deny-all-button').click()
    await page.getByRole('button', { name: 'Weiter zur Dateneingabe' }).click()

    if (express) {
      await page.locator('#vorname').fill(randomize('Ralf'));
      await page.locator('#nachname').fill('Ratenkauf');
    }

    await page.locator('#geburtsdatum').fill('05.04.1972')

    if (express) {
      await page.locator('#email').fill('ralf.ratenkauf@teambank.de')

    }
    await page.locator('#mobilfunknummer').fill('015112345678')
    await page.locator('#iban').fill('DE12500105170648489890')

    if (express) {
      await page.locator('#strasseHausNr').fill('Beuthener Str. 25')
      await page.locator('#plz').fill('90402')
      await page.locator('#ort').fill('Nürnberg')
    }

    await page.getByText('Allen zustimmen').click()

    await delay(500)
    await page.getByRole('button', { name: 'Ratenwunsch prüfen' }).click()

    await delay(500)
    await page.getByRole('button', { name: 'Ratenwunsch übernehmen' }).click()
  })
}

const confirmOrder = async (page) => {
  await test.step(`Confirm order`, async() => {
    /* Confirm Page */
    //await page.getByLabel('Please accept the terms').check();
    await page.getByRole('button', { name: 'Jetzt kaufen' }).click();

    /* Success Page */
    await expect(page.getByText('Vielen Dank für Ihre Bestellung!')).toBeVisible()
  })
}

const goToProduct = async (page, num = 0) => {
  await test.step(`Go to product (num: ${num}}`, async() => {
    await page.goto('index.php/product.html');
  })
}

/*
test('Check settings', async ({ page }) => {
  await page.goto('/admin/');
  await page.getByPlaceholder('Benutzername').fill('admin');
  await page.getByPlaceholder('Passwort').fill('admin1234578!');
  await page.getByRole('button', { name: 'Anmelden' }).click();

  await page.getByRole('link', { name: 'Shops' }).click();
  await page.getByRole('link', { name: 'Konfiguration' }).click();
  await page.getByRole('tab', { name: 'Verkäufe' }).getByRole('strong').click();
  await page.getByRole('tab', { name: 'Zahlungsarten' }).click();

  await page.locator('[data-test-id="easycredit-config-button"]').click();

  await page.getByRole('link', { name: 'Zugangsdaten überprüfen' }).click();
  await page.getByText('Die Zugangsdaten sind gültig.').click();

});
*/
test.describe('Go through standard checkout', () => {
  test('standardCheckout', async ({ page }) => {

    await goToProduct(page)

    await page.getByRole('button', { name: 'In den Warenkorb' }).first().click()
    await expect(page.getByText('Sie haben Product zu Ihrem Warenkorb hinzugefügt.')).toBeVisible();

    await page.goto('index.php/checkout/')

    var randomLetters = '';
    for (let i = 0; i < 3; i++) {
      randomLetters += String.fromCharCode(97+Math.floor(Math.random() * 26));
    }
    await page.getByRole('textbox', { name: 'Vorname' }).fill(randomize('Ralf'))
    await page.getByRole('textbox', { name: 'Nachname' }).fill('Ratenkauf')

    await page.getByRole('textbox', { name: 'E-Mail' }).fill('test@email.com');

    await page.getByLabel('Straße: Line 1').fill('Beuthener Str. 25')

    await page.getByLabel('Land', { exact: true }).selectOption('DE');
    await page.locator('select[name="region_id"]').selectOption('81');

    await page.getByRole('textbox', { name: 'Stadt' }).fill('Nürnberg')
    await page.getByRole('textbox', { name: 'PLZ' }).fill('90402')
    await page.getByLabel('Telefonnummer').fill('01703404848');

    /* select shipping method */
    await page.getByLabel('Fixed').click()

    await page.getByRole('button', { name: 'Weiter' }).click();

    /* Confirm Page */
    await page.locator('easycredit-checkout-label').click()
    await page.getByRole('button', { name: 'Weiter zum Ratenkauf' }).click()
    await page.locator('span:text("Akzeptieren"):visible').click();

    await goThroughPaymentPage(page)
    await confirmOrder(page)
  });
});

test.describe('Go through express checkout', () => {
  test('expressCheckout', async ({ page }) => {

    await goToProduct(page)

    await page.locator('a').filter({ hasText: 'Jetzt direkt in Raten zahlen' }).click();
    await page.getByText('Akzeptieren', { exact: true }).click();

    await goThroughPaymentPage(page, true)
    await confirmOrder(page)
  });
});
