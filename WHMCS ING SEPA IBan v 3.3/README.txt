=========================================

INSTALLATIE CERTIFICATEN:
U dient volgens de documentatie van ING zelf certificaten aan te maken.
Hiervoor verwijzen we u naar de officiele documentatie van ING waar deze procedure staat uitgelegd in paragraaf 3.5,
https://idealtest.secure-ing.com/ideal/downloadFile.do?filename=/docs/ing/iDEAL_Advanced_PHP_EN_v2.5.pdf

Onthoud goed het wachtwoord dat u heeft gebruikt. Deze heeft u verderop nodig.

Wanneer u deze procedure heeft doorlopen heeft u 2 bestanden
- priv.pem
- cert.cer

Deze bestanden heeft u verderop tijdens de installatie nodig.
Het cert.cer bestand dient u tevens te uploaden binnen uw ING Dashboard.

=========================================

INSTALLATIE HANDLEIDING BETAALMODULE:
1) Pak de zipfile lokaal uit
2) Ga naar de map "/modules/gateways/ingidealadvanced/includes/security" en kopieer naar deze locatie uw eerder aangemaakte priv.pem en cert.cer bestand.
3) Open het bestand "config.conf"
4) Vul hier de volgende gegevens in:
- MERCHANTID (Dit is uw Acceptant ID. Deze vindt u op uw ING dashboard)
- SUBID (Standaard 0)
- MERCHANTRETURNURL (Uw WHMCS URL)

- PRIVATEKEYPASS (Wachtwoord waarmee u de certificaten heeft aangemaakt.

5) Upload de bestanden naar de root / basismap van je WHMCS installatie.
6) Log in op je WHMCS installatie
7) Ga naar "Setup" -> "Payments" -> "Payment Gateways"
8) Kies de module "ING iDEAL advanced" uit de dropdown en activeer deze
9) Vul je "Transactiekosten %" in (dit is een vrij instelbaar bedrag. Let op: De klant ziet dit bedrag alleen op het afrekenscherm van de Rabobank. U dient de klant vooraf zelf te melden dat deze transactiekosten worden toegevoegd!)
10) Vul je "Transactiekosten €" in (dit is een vrij instelbaar bedrag. Let op: De klant ziet dit bedrag alleen op het afrekenscherm van de Rabobank. U dient de klant vooraf zelf te melden dat deze transactiekosten worden toegevoegd! De transactiekosten in % worden eerst berekend.)


=========================================

TESTEN VAN DE MODULE:
De module heeft ook een testmodus. Deze testmodus dient u te gebruiken om de verplichte testprocedures van ING te doorlopen.
Deze gebruikt de testomgeving van de ING Bank. Voor meer informatie over de testprocedures verwijzen wij u naar de officiele documentatie van ING
https://idealtest.secure-ing.com/ideal/downloadFile.do?filename=/docs/ing/iDEAL_Advanced_PHP_EN_v2.5.pdf

Let op!: Je kan dus daadwerkelijk orders als betaald markeren wanneer je de transactie goedkeurt. 
Doe dit dus met een testfactuur en niet met echte klantfacturen.

=========================================
