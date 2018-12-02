Nastavení
* na řádku 8 v souboru /app/bootstrap.php doplnit ostrou doménu
* na řádku 9 v souboru /app/bootstrap.php doplnit tajnou frázi - slouží pro debugování na serveru a snadné mazání cache z lišty
* nastavit oprávnění pro zápis (nejdříve 775, jinak 777) pro adresáře /temp a /log
* vymazat temp/cache - po každé změně, možno také provést z Tracy Debug Bar - moje dodělávka pro tebe

Jak to funguje
* /app/presenters/templates/Homepage - vše co se týká úvodní stránky (layout dědí z hlavního layout = stejný desing)
* /app/presenters/templates/Page - vše co se týká stránek (layout dědí z hlavního layout = stejný desing)

Statické stránky:
* umístit do /app/presenters/templates/Page/<adresa stranky bez pripony>.latte - pokud ma byt zanorena tak do slozky