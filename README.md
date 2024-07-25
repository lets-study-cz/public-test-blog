# Archiv Let's Create Blogu
Náš nový PHP Blog, který měl být vydán koncem dubna a nikdy nebyl dokončen. Obsahuje hodně nedokončeného kódu, chyb, špatné optimalizace a dalších problémů.

Rozhodli jsme se vydat zdrojový kód Blogu aby posloužil jako základní reference lidem, kteří si chtějí postavit vlastní CMS systém na PHP, ale nemohou se moc inspirovat.

# Použito
- Parsedown pro Markdown podporu psaní článků a renderování ve view_article.php
- PHP 8.X
- Vanilla JavaScript pro automatické ukládání článků do složky /posts/, načítání apod.

# Co vše jsme se rozhodli zveřejnit
- Hlavní stránky Blogu (hlavní stránka, vyhledávání, všechny články a stránku pro zobrazení článku)
- CMS panel pro redaktory (správa článků, psaní článků s podporou automatického ukládání každých XY sekund, správa uživatelů, tagy, uživatelské role, ...)

# Co chybí?
- db.php soubor (obsahoval údaje k připojení na databázi - můžete si jeden vytvořit sami když si tam uděláte základní spojení se serverem)
- Archivační složka pro příspěvky, které jsou "v draftu", stažené z publikace nebo archivované (přišlo nám lepší je spíše archivovat než mazat)
- Vendor složka s PHP Parsedownem. Ten si musíte stáhnout přes `composer install ...`

*Rádi bychom upozornili, že kód v tomto repozitáři není finální a jedná se pouze o demonstrativní ukázku. Kód je špatně optimalizovaný a proto je dobré se pouze inspirovat a navrhnout si vlastní řešení. Za použití našeho kódu ve vašich stránkách neneseme odpovědnost (proto to nedoporučujeme používat).*
