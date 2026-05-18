# easyCredit-Rechnung & Ratenkauf for Magento 2 - Business Services von easyCredit

Wir bieten Ihren Kunden flexible, transparente und sichere Zahlungsmöglichkeiten.
Sie erhalten Zugang zu einer führenden Raten- und Rechnungsoptionen, die Ihren Umsatz ankurbeln und die Kundenzufriedenheit steigern.
🏆 Ausgezeichnet als **“Leader Payment” von OMR Reviews im Q1/25**.

🚀 Erweitern Sie mit **easyCredit-Ratenkauf** und **easyCredit-Rechnung** Ihr Paymentangebot.
Vertrauen Sie auf unsere langjährige Erfahrung im Liquiditätsmanagement und die bewiesene hohe Kundenzufriedenheit mit dem easyCredit.

## 🔍 Unsere Lösungen im Detail:

### 🛍️   easyCredit-Ratenkauf:

- Für Warenkörbe von **200 Euro bis 10.000 Euro**
- Frei wählbare Laufzeiten von **2 bis 60 Monate**

### 🧾 easyCredit-Rechnung:

- Für Warenkörbe von **50 Euro bis 5.000 Euro**
- **Schnelle Auszahlung** für Sie trotz **30 Tage Zahlpause** für Ihre Kunden

## Ihre Vorteile:

- ✅ **Einfach** – Ein Plugin für beide Zahlarten
- ⚖️ **Fair** – Einfaches, transparentes Preismodell
- 🔐 **Sicher** – Wir übernehmen das volle Ausfallrisiko

## Vorteile für den Endkunden:

- ⚡ **Sofort** – Sofortige Entscheidung im Zahlungsvorgang. Ganz bequem ohne PostIdent-Verfahren
- 🧩 **Einfach** – Direkter Abschluss ohne App und Login
- 🔁 **Flexibel** – Vorzeitige Rückzahlung des Ratenkaufkunden möglich

# Getting started
Are you interested in using easyCredit-Ratenkauf? Contact us now:
* [sales.ratenkauf@easycredit.de](https://store.shopware.com/en/easyc36021249341f/ratenkauf-by-easycredit.html#)
* +49 (0)911 5390 2726

or register at [easycredit-ratenkauf.de](https://www.easycredit-ratenkauf.de/registrierung.htm) and we will contact you.

**Please note that a valid contract is required to use the plug-in.**

# Installation

The easyCredit extension for Magento 2 can be installed using Composer or by manually copying files. If files are copied manually, the API library has to be installed separately using Composer.

## Composer Installation 

Go to your Magento 2 installation directory and run the following commands:

	composer require teambank/easycredit-plugin-magento-2
	php bin/magento setup:upgrade
	php bin/magento setup:di:compile
	php bin/magento cache:clean

Please also follow the guidelines in our [Documentation](https://netzkollektiv.com/docs/easycredit-magento2/)

# Compatibility

[![Test](https://github.com/teambank/easycredit-plugin-magento-2/actions/workflows/test.yml/badge.svg)](https://github.com/teambank/easycredit-plugin-magento-2/actions/workflows/test.yml)

This extension aims to be as compatible as possible with current, future versions of Magento 2. This version is tested with:

* Magento 2.4.x 

Earlier versions of Magento 2 may work, but are not actively tested anymore. For earlier versions of Magento or PHP < 7.4 based systems please try to use v1.3.10.

If you still have any problems, please open a ticket or contact [ratenkauf@easycredit.de](mailto:ratenkauf@easycredit.de).

## Hyvä Checkout

Für den **Hyvä React Checkout** gibt es eine separate Integration:

[netzkollektiv/magento2-react-checkout-easycredit](https://github.com/netzkollektiv/magento2-react-checkout-easycredit)

Das Repository kann als Ausgangspunkt dienen, ist aber möglicherweise nicht mehr auf dem aktuellen Stand.

# License

* [MIT](https://opensource.org/licenses/MIT)

# Security
If you have discovered a security vulnerability, please email [opensource@teambank.de](mailto:opensource@teambank.de).
