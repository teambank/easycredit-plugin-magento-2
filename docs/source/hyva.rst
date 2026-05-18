================
Hyvä Theme & Checkout
================

Diese Extension ist für den **Standard-Checkout von Magento 2** (Luma / Knockout-Checkout) entwickelt und getestet. Widgets, Payment-Komponenten und der Checkout-Ablauf setzen auf dieses Frontend voraus.

Hyvä Theme
----------

Wenn Sie das **Hyvä Theme** nutzen, aber den **klassischen Magento-Checkout** (nicht Hyvä React Checkout) beibehalten, kann die Integration in vielen Shops funktionieren, sofern der Checkout weiterhin über die Standard-Checkout-Routen läuft. Dieser Fall wird von uns **nicht aktiv in der CI-Matrix getestet**; prüfen Sie Ratenkauf, Rechnung und Express-Button in Ihrer Staging-Umgebung.

Hyvä React Checkout
-------------------

Für den **Hyvä React Checkout** reicht diese Extension allein **nicht** aus. Es wird eine **zusätzliche Integration** benötigt, die die easyCredit-Zahlarten in den React-Checkout einbindet:

* `netzkollektiv/magento2-react-checkout-easycredit` — https://github.com/netzkollektiv/magento2-react-checkout-easycredit

.. note::

   Beide Pakete sind erforderlich: diese Extension (Backend, API, Konfiguration, Standard-Frontend) **und** die React-Checkout-Erweiterung für die Darstellung im Hyvä Checkout.

Das React-Checkout-Repository dient als **Ausgangspunkt** für die Anbindung; es wird möglicherweise **nicht fortlaufend** mit jeder Version dieser Extension oder des Hyvä React Checkout synchron gehalten. Vor einem Produktivgang sind eigene Tests (Installation, Zahlungsfluss, Bestellabschluss) erforderlich.

Empfohlene Reihenfolge
~~~~~~~~~~~~~~~~~~~~~~

#. ``teambank/easycredit-plugin-magento-2`` wie unter :doc:`installation` beschrieben installieren und konfigurieren (API-Zugangsdaten, Zahlungsarten aktivieren).
#. Die Hyvä-React-Checkout-Integration gemäß der Anleitung im verlinkten Repository ergänzen.
#. Im Test-Modus von easyCredit den kompletten Checkout (Ratenkauf und ggf. Rechnung) durchspielen.

Support
-------

Fragen zur Standard-Integration: `ratenkauf@easycredit.de <mailto:ratenkauf@easycredit.de>`_.

Bei Problemen speziell mit dem Hyvä React Checkout prüfen Sie zuerst Issues und README im React-Checkout-Repository; für die Kern-Extension öffnen Sie bei Bedarf ein Ticket im `Haupt-Repository <https://github.com/teambank/easycredit-plugin-magento-2/issues>`_.
