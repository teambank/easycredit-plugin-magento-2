# Changelog

## v3.0.3

- Express-Checkout: behebt fehlerhafte Adressdarstellung, die durch die Übernahme von API-Gender-Werten in das Magento-Anrede-Feld verursacht wurde

## v3.0.2

- Kompatibilität mit Magento 2.4.9 @ PHP 8.5
- die API-Bibliothek wurde auf ^2.0.8 aktualisiert
- Behebung von PHP-Deprecation-Warnungen (Payment, Setup/Schema)
- Code-Modernisierung für PHPStan 2 und Magento Coding Standard (u.a. Helper `EasyCreditData`, Rückgabetypen)
- CI-Tests für Magento 2.4.2–2.4.9 inkl. Composer 2 für ältere 2.4.x-Versionen
- Playwright-Tests an die Payment Page angepasst
