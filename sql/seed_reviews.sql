-- Seed van de bestaande 7 recensies.
-- Voer uit NA migration1.sql. INSERT IGNORE zorgt dat her-uitvoeren niets stuk maakt
-- (bestaande recensies met hetzelfde sequence_number worden overgeslagen).
-- Importeren via phpMyAdmin: tabblad "Import", dit bestand selecteren, uitvoeren.

USE boekenclub;

-- ===========================================================================
-- #1 — 2001: A Space Oddysey
-- ===========================================================================
INSERT IGNORE INTO reviews
    (sequence_number, book_title, book_author, meeting_date,
     host_name, host_location, attendees, verdict, preview, full_html)
VALUES (
    1,
    '2001: A Space Oddysey',
    'Arthur C. Clarke',
    '2025-02-27',
    'Bart',
    'Garrelsweer',
    'Bart, Nienke, Evert en Erik',
    'positief',
    'Een strak geschreven en vooruitziend science fiction-boek dat een nieuw licht werpt op de minstens zo bekende verfilming ervan. Bart heeft er een mooie recensie over geschreven...',
    '<p>Een strak geschreven en vooruitziend science fiction-boek dat een nieuw licht werpt op de minstens zo bekende <a href="https://www.imdb.com/title/tt0062622" target="_blank">verfilming</a> ervan.
Bart heeft er een mooie <a href="https://mandarin.nl/boeken/2025/#2001-space-odyssey" target="_blank">recensie</a> over geschreven.</p>'
);

-- ===========================================================================
-- #2 — Klara and the Sun
-- ===========================================================================
INSERT IGNORE INTO reviews
    (sequence_number, book_title, book_author, meeting_date,
     host_name, host_location, attendees, verdict, preview, full_html)
VALUES (
    2,
    'Klara and the Sun',
    'Kazuo Ishiguro',
    '2025-04-17',
    'Evert',
    'Groningen',
    'Evert, Jolanda, Linda en Erik',
    'positief',
    'Een subtiel opgebouwd boek dat draait om de vraag wat het betekent om mens te zijn. Daarbij komen thema''s als kunstmatige intelligentie, eugenetica, religie en vriendschap om de hoek kijken...',
    '<p>Een subtiel opgebouwd boek dat draait om de vraag wat het betekent om mens te zijn.
Daarbij komen thema''s als kunstmatige intelligentie, eugenetica, religie en vriendschap om de hoek kijken.
Het verhaal wordt verteld vanuit het standpunt van een ''Artificial Friend'' (AF) genaamd Klara.
Zij is een scherp observator en leert zo veel over het gedrag van mensen.
Klara staat te koop in een winkel vol met AF''s en wordt op een dag meegenomen door het meisje Josie en haar moeder.
Josie is ziekelijk en in eerste instantie krijgen we het idee dat Klara is uitgekozen om Josie gezelschap te houden.
Dan blijkt gaandeweg dat de moeder, door Klara consequent ''the Mother genoemd'', heel andere plannen heeft met Klara.
Ondertussen heeft Klara een soort religie ontwikkeld die draait om de Zon (wat niet gek is voor een robot die een accu op zonlicht heeft).
Klara hoopt dat de Zon Josie wil genezen, al beseft ze dat dat een offer zal vragen.
Grappig is dat dit boek voor velen van ons het eerste boek was dat ze lazen dat door een Nobelprijswinnaar is geschreven!
Een ander boek van Ishiguro, ''The Remains of the Day'', was wel bekend maar vooral vanwege <a href="https://www.imdb.com/title/tt0107943/" target="_blank">de verfilming met o.a. Anthony Hopkins</a>.</p>
<p>Een korte tijd later heeft Bart ook van dit boek een <a href="https://mandarin.nl/boeken/2025/klara-and-the-sun.html" target="_blank">recensie</a> geschreven.</p>'
);

-- ===========================================================================
-- #3 — The Mountain in the Sea
-- ===========================================================================
INSERT IGNORE INTO reviews
    (sequence_number, book_title, book_author, meeting_date,
     host_name, host_location, attendees, verdict, preview, full_html)
VALUES (
    3,
    'The Mountain in the Sea',
    'Ray Nayler',
    '2025-07-03',
    'Erik',
    'Paterswolde',
    'Bart, Jan, Jolanda, Linda en Erik',
    'gemengd',
    'The Mountain in the Sea is een rijk boek, gevuld met diverse thema''s en maar liefst drie verhaallijnen (die pas helemaal op het einde samenkomen). Het speelt in een redelijke nabije toekomst...',
    '<p>The Mountain in the Sea is een rijk boek, gevuld met diverse thema''s en maar liefst drie verhaallijnen (die pas helemaal op het einde samenkomen).
Het speelt in een redelijke nabije toekomst. In een afgeschermde lagune in het huidige Vietnam wordt een octopus-soort ontdekt
die een voor mensen herkenbare vorm van intelligentie heeft ontwikkeld. Ze gebruiken symbolen en kunnen daar zelfs een soort taal mee vormen.
Hiermee kunnen ze kennis doorgeven aan de volgende generatie, wat essentieel is omdat octopussen vrij kort leven.
De hoofdpersoon, dr. Ha Nguyen, probeert <i>first contact</i> te maken met deze wezens.
Ondertussen liggen er grote bedrijven en andere organisaties op de loer om met deze ontdekking aan de haal te gaan.</p>
<p>Zoals gezegd zijn er meer verhaallijnen: we volgen ook de jonge Japanner Eiko die tot slaaf gemaakt is aan boord van een door AI aangedreven vistrawler,
en een Russische hacker die probeert in te breken in het brein van ''s werelds eerste androïde, Evrim.
Pas helemaal aan het einde komen de lijnen samen in een soort episch eindgevecht, waarbij de octopussen ook nog eens toeslaan en zich tegen de mensen keren.
Dit is meteen ook een belangrijk punt van kritiek: heel veel gebeurt pas op het einde en het nut van de extra verhaallijnen is beperkt.
Misschien is het omdat het Naylers debuut is, maar ons inziens had hij er beter drie losse boeken (of één boek en twee korte verhalen) van kunnen maken.
Ook de intermezzo''s, citaten uit (fictionele) boeken van dr. Nguyen en het hoofd van de multinational DIANIMA dr. Mínervudóttir-Chan (wat een naam),
gingen sommigen na een tijdje op de zenuwen werken.</p>
<p>Het belangrijkste thema is intelligentie. Wanneer kun je daarvan spreken? Hoe kun je vaststellen of iets niets-menselijks, zoals een octopus of een androïde, intelligent is?
Ook het geheugen komt veel aan bod. De androïde Evrim kan niets vergeten en juist dat maakt hem onmenselijk.
Eiko gebruikt de eeuwenoude techniek van het Geheugenpaleis om niets te vergeten van zijn ontvoering en helse tocht aan boord van de <i>Sea Wolf</i>.</p>
<p>Kortom: een interessant maar overvol boek, dat eigenlijk teveel probeert te vertellen.</p>
<p>Ook over dit boek heeft Bart <a href="https://mandarin.nl/boeken/2025/the-mountain-in-the-sea.html" target="_blank">een uitgebreide recensie</a> geschreven,
met veel aandacht voor niet-menselijke intelligentie en ons (on)vermogen om ons daar een beeld van te kunnen vormen.</p>'
);

-- ===========================================================================
-- #4 — Stories of your Life and others
-- ===========================================================================
INSERT IGNORE INTO reviews
    (sequence_number, book_title, book_author, meeting_date,
     host_name, host_location, attendees, verdict, preview, full_html)
VALUES (
    4,
    'Stories of your Life and others',
    'Ted Chiang',
    '2025-09-11',
    'Jan',
    'Groningen',
    'Bart, Jan, Jolanda, Linda en Erik',
    'gematigd positief',
    'Onze vierde boek was een verhalenbundel. Een lastige vorm voor een boekenclub, omdat elk verhaal tijd kost om erin te komen. Immers bij een roman hoef je die "investering" maar één keer te doen...',
    '<p>Onze vierde boek was een verhalenbundel. Een lastige vorm voor een boekenclub, omdat elk verhaal tijd kost om erin te komen.
Immers bij een roman hoef je die "investering" maar één keer te doen.</p>
<p>We zijn de verhalen in engszins willekeurige volgorde bij langs gegaan (dat is dan wel weer een voordeeld van een bundel).
Elk verhaal riep wel associaties op met films en/of andere boeken, met als meest prominente voorbeeld <a href="https://www.imdb.com/title/tt2543164/" target="_blank">de film "Arrival"</a> die gebaseerd is op het titelverhaal.
Het was interessant om te zien welke aanpassingen er gemaakt zijn om het gecompliceerde verhaal geschikt te maken voor het medium film.</p>
<p>Elk verhaal is gebaseerd op een aanname, die vervolgens tot in de puntjes wordt uitgewerkt. En ondanks dat het <i>science fiction</i> wordt genoemd,
kunnen die aannames heel anders en verrassend zijn: wat zou er bijvoorbeeld gebeuren als het verhaal over de Toren van Babel echt zou zijn?
Of wat als de mensheid in de toekomst voorbij wordt gestreefd door genetisch verbeterde "meta-humans"? Of als engelen echt bestaan en je geloof op de proef stellen met wonderen en rampen?
In sommige verhalen, zoals het titelverhaal, pakt dit heel goed uit. In andere minder, zoals het verhaal "72 letters" waarin een 19e-eeuws Engeland wordt geschetst
waarin automata bestaan die "bezield" kunnen worden door ze een naam te geven
(met een heel duidelijke link naar <a href="https://nl.wikipedia.org/wiki/Golem_(legende)" target="_blank">het verhaal van de Golem</a>).
In dit laatste verhaal probeert Chiang (te) veel best wel complexe dingen te vertellen, wat de leesbaarheid en het leesplezier niet ten goede komt.
Het is overigens bijzonder te noemen dat een auteur met een Taiwanees-Chinese achtergrond voor zoveel verhalen een oudtestamentisch of joods uitgangspunt neemt.</p>
<p>Een gevarieerde bundel met superoriginele en -intelligente verhalen, waarvan sommige fantastisch uitpakken en andere minder.
En uiteraard heeft ons aller Bart ook over dit boek <a href="https://mandarin.nl/boeken/2025/stories-of-your-life.html" target="_blank">een eigen recensie</a> geschreven.</p>'
);

-- ===========================================================================
-- #5 — Playground (NL: 'Vrij spel')
-- ===========================================================================
INSERT IGNORE INTO reviews
    (sequence_number, book_title, book_author, meeting_date,
     host_name, host_location, attendees, verdict, preview, full_html)
VALUES (
    5,
    'Playground (NL: ''Vrij spel'')',
    'Richard Powers',
    '2025-11-13',
    'Nienke',
    'Winschoten',
    'Nienke, Bart, Jan, Jolanda en Erik',
    'positief',
    'Deze roman van Powers is het boek dat tot nu toe het meeste enthousiasme heeft losgemaakt bij onze boekenclub. Bijna iedereen zou ''m 5 sterren geven.',
    '<p>Deze roman van Powers is het boek dat tot nu toe het meeste enthousiasme heeft losgemaakt bij onze boekenclub.
Bijna iedereen zou ''m 5 sterren geven; alleen Jolanda ging voor 4 omdat zij soms warmte/liefde miste bij de hoofdpersonen.
Het is hoe dan ook een ongelooflijk rijk boek, wat al bleek uit de reeks thema''s die Nienke opsomde bij haar inleiding van de avond.
Een kleine greep daaruit om een idee te geven: vervuiling van de oceanen, racisme, ongelijkheid man-vrouw, AI, Big Tech, kolonialisme...
Klinkt als zware kost, maar dankzij het schrijverschap van Powers is het dat zeker niet!
Zoals altijd heeft Bart weer <a href="https://mandarin.nl/boeken/2025/vrij-spel.html" target="_blank">een treffende recensie geschreven op zijn blog</a>
en ook <a href="https://boekenkrant.com/recensie/playground-2/" target="_blank">deze recensie van Nico van der Sijde</a>, naar blijkt een oud-collega van ons, is zeer de moeite waard.</p>'
);

-- ===========================================================================
-- #6 — Sea of Tranquility (nog te schrijven)
-- ===========================================================================
INSERT IGNORE INTO reviews
    (sequence_number, book_title, book_author, meeting_date,
     host_name, host_location, attendees, verdict, preview, full_html)
VALUES (
    6,
    'Sea of Tranquility',
    'Emily St. John Mandel',
    '2026-04-08',
    'Jolanda',
    'Groningen',
    'Jolanda, Bart, Jacob, Jan, Linda en Erik',
    'positief',
    NULL,
    NULL
);

-- ===========================================================================
-- #7 — Black Matter (nog te plannen / schrijven)
-- ===========================================================================
INSERT IGNORE INTO reviews
    (sequence_number, book_title, book_author, meeting_date,
     host_name, host_location, attendees, verdict, preview, full_html)
VALUES (
    7,
    'Black Matter',
    'Blake Crouch',
    '2026-05-28',
    'Jacob',
    'Groningen',
    NULL,
    NULL,
    NULL,
    NULL
);
