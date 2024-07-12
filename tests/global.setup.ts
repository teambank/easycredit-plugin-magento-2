import { chromium, request, type FullConfig } from "@playwright/test";

export async function globalSetup() {
  console.log("[prepareData] preparing test data in store");

  var headers = {
    "Content-Type": "application/json",
    Accept: "application/json",
  };

  const req = await request.newContext();

  var response = await req.post(
    "/rest/default/V1/integration/admin/token",
    {
      headers: headers,
      data: {
        username: "admin",
        password: "admin1234578!",
      },
    }
  );
  const authorization = await response.json();
  headers["Authorization"] = "Bearer " + authorization;
  console.log(`[prepareData] logged in with ${authorization}`);

  response = await req.post("/rest/V1/categories", {
    headers: headers,
    data: {
      category: {
        parent_id: 2,
        name: "Products",
        is_active: 1,
      },
    },
  });
  var categoryData = await response.json();
  let categoryId = categoryData.id;
  if (!categoryId) {
    response = await req.get("/rest/V1/categories", { headers: headers });
    var categoryData = await response.json();
    categoryId = categoryData.children_data[0].id;
  }
  console.log(`[prepareData] added category with id ${categoryId}`);

  const baseProductData = {
    attribute_set_id: 4,
    status: 1,
    visibility: 4,
    type_id: "simple",
    weight: "1",
    extension_attributes: {
      category_links: [
        {
          position: 0,
          category_id: categoryId,
          extension_attributes: null,
        },
      ],
      stock_item: {
        qty: "99999",
        is_in_stock: true,
      },
    },
  };

  const productsData = [
    { sku: "regular", name: "Regular Product", price: 201 },
    { sku: "below50", name: "Below 50", price: 49 },
    { sku: "below200", name: "Below 200", price: 199 },
    { sku: "above5000", name: "Above 5000", price: 6000 },
    { sku: "above10000", name: "Above 10000", price: 10000 },
  ];

  for (const productData of productsData) {
    var response = await req.post("/rest/V1/products", {
      headers: headers,
      data: {
        product: {
          ...baseProductData,
          ...productData,
        },
      },
    });
    console.log(`[prepareData] added product ${productData.sku}`);
  }
};
