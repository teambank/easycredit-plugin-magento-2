import { defineConfig, devices, PlaywrightTestConfig } from "@playwright/test";
import { seconds } from "./utils";

let config: PlaywrightTestConfig = {
  outputDir: "../test-results/" + process.env.VERSION + "/",
  use: {
    baseURL: process.env.BASE_URL ?? "http://localhost",
    trace: "on",
  },
  globalSetup: require.resolve("./global-setup"),
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
      name: "backend",
      use: { ...devices["Desktop Chrome"] },
      testMatch: "backend.spec.ts",
      dependencies: ["backend-auth", "checkout"],
    },
  ],
};

if (!process.env.BASE_URL) {
  config = {
    ...config,
    ... {
      webServer: {
        command: 'PHP_CLI_SERVER_WORKERS=8 sudo php -S localhost:80 -t /opt/wordpress',
        url: 'http://localhost/',
        reuseExistingServer: !process.env.CI,
        stdout: 'ignore',
        stderr: 'pipe',
        timeout: 5 * 1000
      }
    }
  }
}

export default defineConfig(config)
