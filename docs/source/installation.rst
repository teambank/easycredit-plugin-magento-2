Installation
============

Composer Installation
------------------------------

* Installieren Sie die Extension mit Composer:

.. code-block:: bash

    $ composer require teambank/easycredit-plugin-magento-2

* Führen Sie die folgenden Kommandos als Webserver-User auf der Kommandozeile aus:

.. code-block:: bash

    $ php bin/magento module:enable Netzkollektiv_EasyCredit
    $ php bin/magento setup:upgrade
    $ php bin/magento setup:di:compile
    $ php bin/magento setup:static-content:deploy
    $ php bin/magento cache:clean

Wenn Sie die Zugangsdaten bereits zur Hand haben führen Sie noch die folgenden Kommandos aus:

.. code-block:: bash

    $ php bin/magento config:set payment/easycredit/credentials/api_key 2.de.1234.4321
    $ php bin/magento config:set payment/easycredit/credentials/api_token abc-def-ghi
    $ php bin/magento cache:clean

* Loggen Sie sich aus dem Magento Admin Panel aus und wieder ein
* Das Modul steht Ihnen nun zur Verfügung

manuelle Installation via SSH
------------------------------

* Laden Sie die Extension von der `Github Release Page <https://github.com/teambank/easycredit-plugin-magento-2>`_ herunter
* Entpacken Sie die Extension in ein temporäres Verzeichnis
* Kopieren Sie die Extension nach `app/code/Netzkollektiv/EasyCredit` in Ihrer Magento Installation

.. code-block:: bash

    $ mkdir app/code/Netzkollektiv/EasyCredit
    $ unzip -d app/code/Netzkollektiv/EasyCredit m2-easycredit-x.x.x.zip

* Installieren Sie das easyCredit PHP SDK über Composer:

.. code-block:: bash

    $ composer require netzkollektiv/easycredit-api-v3-php

Fahren Sie ansonsten fort wie bei der :ref:`Composer Installation`.

Magento Marketplace
-------------------

Die Version auf Magento Marketplace ist derzeit nicht aktuell. Bitte installieren Sie das Modul entweder über Composer oder manuell.
