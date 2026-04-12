-- Voegt een optioneel 'aantal pagina's'-veld toe aan het voordraagformulier.
-- Voer uit op bestaande installaties die al een book_suggestions-tabel hebben.

ALTER TABLE book_suggestions
    ADD COLUMN pages SMALLINT UNSIGNED DEFAULT NULL AFTER url;
