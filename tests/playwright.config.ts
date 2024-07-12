import { defineConfig, devices, PlaywrightTestConfig } from "@playwright/test";
import { seconds } from "./utils";

let config: PlaywrightTestConfig = {
  outputDir: "../test-results/" + process.env.VERSION + "/",
  use: {
    baseURL: process.env.BASE_URL ?? "http://localhost",
    trace: "retain-on-failure",
    locale: "de-DE",
  },
  retries: process.env.CI ? 2 : 0,
  timeout: seconds(40),
  projects: [
    {
      name: "backend-auth",
      use: { ...devices["Desktop Chrome"] },
      testMatch: /.*\.setup\.ts/,
    },
    {
      name: "checkout",
      use: { ...devices["Desktop Chrome"] },
      testMatch: "checkout.spec.ts",
    },
    {
      name: "frontend",
      use: { ...devices["Desktop Chrome"] },
      testMatch: "frontend.spec.ts",
    },
    {
      name: "backend",
      use: { ...devices["Desktop Chrome"] },
      testMatch: "backend.spec.ts",
      dependencies: ["backend-auth", "checkout"],
    },
  ],
  reporter: [["list", { printSteps: true }], ["html"]],
  globalSetup: require.resolve("./global.setup")
};

if (!process.env.BASE_URL) {
  config = {
    ...config,
    ... {
      webServer: {
        command: 'PHP_CLI_SERVER_WORKERS=8 sudo php -S localhost:80 -t /opt/magento/pub /opt/magento/phpserver/router.php',
        url: 'http://localhost/',
        reuseExistingServer: !process.env.CI,
        stdout: 'ignore',
        stderr: 'pipe',
        timeout: 10 * 1000
      }
    }
  }
}

export default defineConfig(config)
