=========================================


ALGEMEEN:
Deze module komt met verschillende betaalgateways:
 - Rabobank Omnikassa (deze optie laat u kiezen welke betaalmethodes u wilt voeren en laat daaruit vervolgens de klant een keuze maken)
 - Rabobank Omnikassa iDEAL (deze optie gebruikt alleen iDEAL, de klant slaat de keuze voor een betaalmethode over en gaat direct naar het respectievelijke betaalscherm)
 - Rabobank Omnikassa VISA (deze optie gebruikt alleen VISA, de klant slaat de keuze voor een betaalmethode over en gaat direct naar het respectievelijke betaalscherm)
 - Rabobank Omnikassa Mastercard (deze optie gebruikt alleen Mastercard, de klant slaat de keuze voor een betaalmethode over en gaat direct naar het respectievelijke betaalscherm)
 - Rabobank Omnikassa Maestro (deze optie gebruikt alleen Maestro, de klant slaat de keuze voor een betaalmethode over en gaat direct naar het respectievelijke betaalscherm)
 - Rabobank Omnikassa Minitix (deze optie gebruikt alleen Minitix, de klant slaat de keuze voor een betaalmethode over en gaat direct naar het respectievelijke betaalscherm)
 - Rabobank Omnikassa Acceptgiro (deze optie gebruikt alleen Acceptgiro, de klant slaat de keuze voor een betaalmethode over en gaat direct naar het respectievelijke betaalscherm)
 - Rabobank Omnikassa Rembours (deze optie gebruikt alleen Rembours, de klant slaat de keuze voor een betaalmethode over en gaat direct naar het respectievelijke betaalscherm)
 - Rabobank Omnikassa Incasso (deze optie gebruikt alleen Incasso, de klant slaat de keuze voor een betaalmethode over en gaat direct naar het respectievelijke betaalscherm)
 - Rabobank Omnikassa MisterCash (deze optie gebruikt alleen MisterCash, de klant slaat de keuze voor een betaalmethode over en gaat direct naar het respectievelijke betaalscherm)
 - Rabobank Omnikassa vPay (deze optie gebruikt alleen vPay, de klant slaat de keuze voor een betaalmethode over en gaat direct naar het respectievelijke betaalscherm)
 
Let op: Acceptgiro, Rembours en Incasso markeren de facturen NIET als betaald. U dient deze betalingen zelf te controleren en in te boeken.


INSTALLATIE HANDLEIDING:

1) Pak de zipfile uit
2) Upload de bestanden naar de root / basismap van je WHMCS installatie.
3) Log in op je WHMCS installatie
4) Ga naar "Setup" -> "Payments" -> "Payment Gateways"
5) Kies de module "Rabobank Omnikassa" uit de dropdown en activeer deze
6) Vul je Winkel ID in (te vinden op je Rabobank Omnikassa dashboard)
7) Vul je Interface versie in (dit is standaard "HP_1.0" tenzij de Rabobank je heeft geinstrueert deze te wijzigen)
8) Vul je Sleutel in (te vinden op je Rabobank Omnikassa dashboard)
9) Vul je Sleutelversie in (te vinden op je Rabobank Omnikassa dashboard)
10) Vul je "Transactiekosten %" in (dit is een vrij instelbaar bedrag. Let op: De klant ziet dit bedrag alleen op het afrekenscherm van de Rabobank. U dient de klant vooraf zelf te melden dat deze transactiekosten worden toegevoegd!)
11) Vul je "Transactiekosten €" in (dit is een vrij instelbaar bedrag. Let op: De klant ziet dit bedrag alleen op het afrekenscherm van de Rabobank. U dient de klant vooraf zelf te melden dat deze transactiekosten worden toegevoegd! De transactiekosten in % worden eerst berekend.)

Opmerking: Mocht er nog geen sleutel aanwezig zijn op je Rabobank Dashboard, dan kan je middels een knop een nieuwe sleutel genereren.

Let op: Achter de sleutel staat een datum wanneer deze sleutel actief is. De module in combinatie met de door jou gekozen sleutel werkt dus pas correct vanaf die datum. De datum is helaas niet zelf in te geven, maar wordt bepaald door de Rabobank. De datum staat meestal 2 weken in de toekomst na het genereren om zo een verplichte testperiode in te lassen. Je kan eventueel telefonisch contact opnemen om deze datum aan te laten passen.


TESTEN VAN DE MODULE:
De module heeft ook een testmodus.
Deze gebruikt onderwater het test Winkel ID en de test sleutel van de Rabobank zelf.

Tijdens het gebruik van de testmodus kan je het beste kiezen voor MiniTix. Na het kiezen van deze betaalmethode op het Rabobank scherm krijg je namelijk zelf de keuze wat je met de transactie wilt doen (bijv. goedkeuren, afkeuren, etc)

Let op!: Je kan dus daadwerkelijk orders als betaald markeren wanneer je de transactie goedkeurt. 
Doe dit dus met een testfactuur en niet met echte klantfacturen.


=========================================


GENERAL NOTE:
This module comes with several payment gateways:
 - Rabobank Omnikassa (this one lets you choose which payment methods you want to allow your client to choose from)
 - Rabobank Omnikassa iDEAL (this one only offers iDEAL, the client will skip the paymentmethod choise and will directly go to the iDEAL payment screen)
 - Rabobank Omnikassa VISA (this one only offers VISA, the client will skip the paymentmethod choise and will directly go to the VISA payment screen)
 - Rabobank Omnikassa Mastercard (this one only offers Mastercard, the client will skip the paymentmethod choise and will directly go to the Mastercard payment screen)
 - Rabobank Omnikassa Maestro (this one only offers Maestro, the client will skip the paymentmethod choise and will directly go to the Maestro payment screen)
 - Rabobank Omnikassa Minitix (this one only offers Maestro, the client will skip the paymentmethod choise and will directly go to the Minitix payment screen)
 - Rabobank Omnikassa Acceptgiro (this one only offers Acceptgiro, the client will skip the paymentmethod choise and will directly go to the Acceptgiro payment screen)
 - Rabobank Omnikassa Rembours (this one only offers Rembours, the client will skip the paymentmethod choise and will directly go to the Rembours payment screen)
 - Rabobank Omnikassa Incasso (this one only offers Incasso, the client will skip the paymentmethod choise and will directly go to the Incasso payment screen.)
 - Rabobank Omnikassa MisterCash (this one only offers MisterCash, the client will skip the paymentmethod choise and will directly go to the MisterCash payment screen)
 - Rabobank Omnikassa vPay (this one only offers vPay, the client will skip the paymentmethod choise and will directly go to the vPay payment screen.)

Note that Acceptgiro, Rembours and Incasso DO NOT mark the invoice as paid. You should check if the payment is made yourself.


SETUP GUIDE:

1) Unpack the zipfile
2) Upload all files to your WHMCS root folder
3) Login to your WHMCS admin panel
4) Go to "Setup" -> "Payments" -> "Payment Gateways"
5) Choose the "Rabobank Omnikassa" module from the dropdown and activate it
6) Fill in your "Winkel ID" (this can be found on your Rabobank Omnikassa Dashboard)
7) Fill in your "Interface versie" (by default this should be "HP_1.0" unless instructed otherwise by Rabobank)
8) Fill in your "Sleutel" (this can be found on your Rabobank Omnikassa Dashboard)
9) Fill in your "Sleutel versie" (this can be found on your Rabobank Omnikassa Dashboard)
10) Fill in your "Transactiekosten %" (this is a surcharge that you can freely set. Note: The client only sees this during the final step on the rabobank screen. You should tell them this surcharge is being calculated yourself.)
11) Fill in your "Transactiekosten €" (this is a surcharge that you can freely set. Note: The client only sees this during the final step on the rabobank screen. You should tell them this surcharge is being calculated yourself. The surcharge in % is calculated first.)

Note: If there isn't a "Sleutel" available on your Rabobank Dashboard, then you can generate one yourself Behind the key is a date on which this particular key will be activated by the Rabobank, The module in combination with your self generated key will only work correctly from that date on. This date is set by the Rabobank. If you want to change it / move it forward you have to call them.


TESTING:
The module also contains a test mode.
This testmode uses the "Winkel ID" and the "Sleutel" from the Rabobank itself so you do not have to fill those in for testing.

Note: Mind that you can actually set orders as paid when you approve transactions during testing. 
You should only use testinvoices to test the module.


=========================================