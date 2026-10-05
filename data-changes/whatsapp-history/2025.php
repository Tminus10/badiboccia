<?php
// WhatsApp history import, season 2025 -- blocks 1-5 approved 2026-10-05. See PROPOSALS.md for the evidence.
// Chat L = Lilo's export. No results are known (chat has none, no plan photo with results);
// only dates. KO: teams + dates (decision D1); QF4 opponent = Patrick & Philipp by decision (group C
// decider winner -- the QF itself was apparently never played, J&P went through), no date.

$open = ['sets' => [null, null], 'played_date' => null, 'game_time' => null];

return [
    'name' => 'whatsapp-history-2025',
    'actor_label' => 'WhatsApp-Import 2025',
    'summary' => 'Season 2025: dates for 39 group games and the bracket as teams + dates (Lilo & Roli champions, no scores), from the WhatsApp chat (Lilo export).',
    'apply' => function (Changeset $c) use ($open): void {
        $d = fn (string $a, string $b, string $date, ?string $time, string $src) => $c->setDate(2025, 'group', $a, $b, $date, $time, $open, $src);

        // Block 1 -- group A
        $d('Guli & Odi', 'Henry & Husi', '2025-06-09', '13:30', 'chat L 07.06.25 Husi "Mo 09.06 2025 13:30 spielt Guli /Odi gegen Henry /Husi"');
        $d('Henry & Husi', 'Sandra & Werni', '2025-06-11', '18:00', 'chat L 09.06.25 Sandra "Mittwuch 11.6 18 Uhr Henry/Husi gägä Werni/Sandra"');
        $d('Guli & Odi', 'Tubä & Wurm', '2025-06-12', '18:00', 'chat L 09.06.25 Guli "Donnerstag 12.6.2025 uf diä 18.00Uhr Toube/Wurm gäg Odi/Guli"');
        $d('Evelyne & Sven', 'Guli & Odi', '2025-06-21', '10:00', 'chat L 16.06.25 Guli "Samschtig uf diä 10i", Evelyne "10.00 setti klappä"');
        $d('Guli & Odi', 'Sandra & Werni', '2025-06-27', '18:30', 'chat L 21.06.25 Sandra "Freytig 27. Juni 18.30 Uhr Walti/Guli gägä Werni/Sandra"');
        $d('Guli & Odi', 'Bruni & Tiz', '2025-06-30', '19:30', 'chat L 28.06.25 Guli "Mändig 30. Juni 19.30 Uhr Bruni/Tiz gäge Odi/Guli", Tiz "perfetto"');
        $d('Henry & Husi', 'Tubä & Wurm', '2025-07-02', '19:00', 'chat L 29.06.25 Husi "Wurm Tube mittwoch 19:00 uhr gegen Hery/Husi"; Wurm "Am Mittwuch isch de dr 2. Juli"');
        $d('Evelyne & Sven', 'Tubä & Wurm', '2025-07-05', '10:00', 'chat L 28.06.25 Evelyne "am samschtig 5.7., 10.00, wurm/tubä-evelyne/sven" (25.06 postponed)');
        $d('Evelyne & Sven', 'Sandra & Werni', '2025-07-10', '19:00', 'chat L 05.07.25 Sandra "Donnerstag 10. Juli 19Uhr Sven/Evelyne gägä Werni/Sandra"');
        $d('Sandra & Werni', 'Bruni & Tiz', '2025-07-13', '11:00', 'chat L 30.06.25 Sandra "Sunntig 13.Juli 11 Uhr Bruni/Tiz gägä Werni/Sandra"');
        $d('Henry & Husi', 'Bruni & Tiz', '2025-07-14', '18:00', 'chat L 02.07.25 Tiz "Mändig 14. Juli, 18.00 Husi/Hendry gägä Tiz/Bruni"');
        $d('Bruni & Tiz', 'Tubä & Wurm', '2025-07-20', '11:00', 'chat L 18.07.25 Tube "Sunntig 20. Juli 11 Uhr Tubä/Wurm gägä Bruni/Tiz"');
        $d('Evelyne & Sven', 'Henry & Husi', '2025-07-22', '18:30', 'chat L 15.07.25 Evelyne "di. 22igschti ab de 18.30", Husi "Ist Ok am dienstag 18:30"');
        $d('Evelyne & Sven', 'Bruni & Tiz', '2025-08-08', '19:00', 'chat L 03.08.25 Sven "Fritig 7ni?", Tiz "Freytig 19.00 isch ok"');

        // Block 2 -- group B
        $d('Lilo & Roli', 'Sarah & Jonas', '2025-06-09', '10:00', 'chat L 05.06.25 Sarah "Mäntig uf die 10ii? Sarah/Jonas", Roli 👍');
        $d('Dani & Peter', 'Sarah & Jonas', '2025-06-24', '18:00', 'chat L 23.06.25 Sarah "morn abig 18:00 uhr sarah/jonas gäge peter/dani"');
        $d('Georges & Stierli', 'Dani & Peter', '2025-06-25', '10:00', 'chat L 23.06.25 Peter "Mittwoch 25.6. 10.00 Uhr Georg/Stierli gegen Dani/Peter", Stierli + Georg 👍');
        $d('Lilo & Roli', 'Dani & Peter', '2025-06-26', '10:00', 'chat L 25.06.25 Dani "Morä Dunschtig … Match 10:00 Uhr: Lilo/Roli - Peter/Dani"');
        $d('Lilo & Roli', 'Zimi & Roman', '2025-07-01', '18:30', 'chat L 26.06.25 Lilo "Di 1.7. 18.30 Zimi/Roman-Lilo/Roli"');
        $d('Georges & Stierli', 'Zimi & Roman', '2025-07-15', '18:30', 'chat L 01.07.25 Zimmi "Dienstag, 15.07., 18:30 Uhr: Stierli / Georges vs. Zimi / Roman"');
        $d('Dani & Peter', 'Zimi & Roman', '2025-07-17', '18:30', 'chat L 12.07.25 Zimmi "Donnerstag, 17.7. um 18:30 Uhr: Peter/Dani vs. Zimi/Roman"');
        $d('Georges & Stierli', 'Sarah & Jonas', '2025-07-19', '10:00', 'chat L 15.07.25 Jonas "samstig am 19.07.2025 am 10.00 uhr", Stierli "Ok 10. Uhr"');
        $d('Georges & Stierli', 'Lilo & Roli', '2025-07-23', '16:00', 'chat L 20.07.25 Lilo "Ok Mittwoch am 4i Georg/Stierli- Roli/Lilo"');
        $d('Sarah & Jonas', 'Zimi & Roman', '2025-08-06', '19:00', 'chat L 05.08.25 Jonas "morn am sibni … sarah und jonas gägä roman und simi"');

        // Block 3 -- group C (#102 left out: rain, uncertain)
        $d('Erika & Debi', 'Siri & Peter', '2025-06-08', '11:00', 'chat L 07.06.25 Erika "Sonntag, 8.6.2025 uf diä 11.00 Uhr: Peter/Siri gäg Debi/Erika"');
        $d('Pädi & Kusi', 'Siri & Peter', '2025-06-19', '11:00', 'chat L 18.06.25 Peter "11i Kusi/Pädi- Siri u Peter", Kusi 👍');
        $d('Attila & Rolf', 'Erika & Debi', '2025-06-19', '13:00', 'chat L 18.06.25 Erika "Morgen, Donnerstag, 13.00 Uhr: Ati/Rolf gegen Debi/Erika"');
        $d('Erika & Debi', 'Pädi & Kusi', '2025-07-03', '18:30', 'chat L 28.06.25 Erika "@ Kusi/ Pädi: Donnstig, 18.30 Uhr wärid mier z\'ha!", Kusi 👍');
        $d('Erika & Debi', 'Patrick & Philipp', '2025-07-29', '19:00', 'chat L 28.07.25 Peter "Morn abig 19ni 00 Erika u Debi mit Patrick u Philipp!"');
        $d('Attila & Rolf', 'Patrick & Philipp', '2025-08-15', '09:00', 'chat L 08.08.25 Peter "Friitig 15.August am 9i Rolf u Evelyn : Patrick u Philipp" (Evelyne for Atti)');
        $d('Pädi & Kusi', 'Patrick & Philipp', '2025-08-28', '19:00', 'chat L 28.08.25 Puppe "Das Entscheidungs-spiel muss bis morgen gespielt sein", Pädi "Ok, demfau hit um 19.00"');

        // Block 4 -- group D
        $d('Jöggu & Puppe', 'Kusi & Muisli', '2025-06-16', '18:00', 'chat L 11.06.25 Muisli "Kusi Montag ja", Jöggu "Perfekt, Montag 18:00"');
        $d('Jöggu & Puppe', 'Moni & Erwin', '2025-06-17', '18:00', 'chat L 12.06.25 Jöggu "Dienstag 18:00 Uhr", Moni "Erwin und Moni hätten Zeit"');
        $d('Kusi & Muisli', 'Moni & Erwin', '2025-06-19', '16:00', 'chat L 16.06.25 Moni "Kusi-Muisli/Erwin Moni Am Donnschtig am 4i Match"');
        $d('Armin & Beat', 'Moni & Erwin', '2025-06-20', '10:00', 'chat L 18.06.25 Moni "Friitig morgä 10 00 Match Armin-Beat-Erwin Moni"');
        $d('Bea & Gysi', 'Armin & Beat', '2025-06-22', '10:00', 'chat L 19.06.25 Armin "Sunntig Morgä 10:00 Uhr. Bea/Roli -Beat/Armin"');
        $d('Armin & Beat', 'Jöggu & Puppe', '2025-06-29', '10:00', 'chat L 25.06.25 Jöggu "passt Sonntag 10:00 Uhr", Armin + Beat "Passt"');
        $d('Bea & Gysi', 'Kusi & Muisli', '2025-07-14', '19:30', 'chat L 07.07.25 Bea "Verschiebedatum zu 99% Mo 14.7. 19.30"; 12.07 "Montag 19.30 Bea /Roli Kusi / muis 14.7."');
        $d('Armin & Beat', 'Kusi & Muisli', '2025-07-30', '18:00', 'chat L 22.07.25 Armin "Mittwuch 30.Juli 25 18:00 Uhr. Thomi/Kusi gägä Beat/Armin"');

        // Block 5 -- bracket (QF1+QF2 -> SF1, QF3+QF4 -> SF2)
        $c->setBracketTeams(2025, 'qf', 1, 'Bea & Gysi', 'Erika & Debi', 'chat L 09.08.25 Erika "Bea/Roli gegen Debi/ Erika"');
        $c->setBracketTeams(2025, 'qf', 2, 'Georges & Stierli', 'Sandra & Werni', 'chat L 14.08.25 Sandra "Georg/Stierli gägä Werni/Sandra"');
        $c->setBracketTeams(2025, 'qf', 3, 'Tubä & Wurm', 'Lilo & Roli', 'chat L 11.08.25 Tube "Spiel tubä Wurm gägä Rolli und lilo"');
        $c->setBracketTeams(2025, 'qf', 4, 'Jöggu & Puppe', 'Patrick & Philipp', 'chat L 29.08.25 Peter Frei "kei Termin … fir Puppe/Jöggu : Patrick/Philipp" (by decision; J&P went through)');
        $c->setDate(2025, 'qf', 'Bea & Gysi', 'Erika & Debi', '2025-08-11', '10:00', $open, 'chat L 09.08.25 Erika "Montag, 11.08.25, 10.00 Uhr Bea/Roli gegen Debi/ Erika" (B&G won)');
        $c->setDate(2025, 'qf', 'Georges & Stierli', 'Sandra & Werni', '2025-08-19', '17:30', $open, 'chat L 14.08.25 Sandra "Dienstag 19.August 17.30 Georg/Stierli gägä Werni/Sandra" (S&W won)');
        $c->setDate(2025, 'qf', 'Tubä & Wurm', 'Lilo & Roli', '2025-08-13', '10:00', $open, 'chat L 11.08.25 Tube "am Mittwoch 13 Aug. 10uhr" (L&R won)');
        $c->setBracketTeams(2025, 'sf', 1, 'Bea & Gysi', 'Sandra & Werni', 'QF1 + QF2 winners');
        $c->setDate(2025, 'sf', 'Bea & Gysi', 'Sandra & Werni', '2025-08-24', '11:00', $open, 'chat L 19.08.25 Sandra "Sunntig 24. August 11Uhr Bea/Gysi gägä Werni/Sandra" (B&G won)');
        $c->setBracketTeams(2025, 'sf', 2, 'Lilo & Roli', 'Jöggu & Puppe', 'QF3 + QF4 winners');
        $c->setDate(2025, 'sf', 'Lilo & Roli', 'Jöggu & Puppe', '2025-09-03', '17:45', $open, 'chat L 01.09.25 Roli "Mi.3.9. Pupe jöggu vs. Lilo roli", Puppe "Mittwoch 17.45Uhr" (L&R won)');
        $c->setBracketTeams(2025, 'third', 1, 'Sandra & Werni', 'Jöggu & Puppe', 'SF losers');
        $c->setDate(2025, 'third', 'Sandra & Werni', 'Jöggu & Puppe', '2025-09-09', '17:30', $open, 'chat L 03.09.25 Sandra "Di 9.9. 17.30 Uhr Jöggu/Puppe gägä Werni / Sandra" (winner unknown)');
        $c->setBracketTeams(2025, 'final', 1, 'Bea & Gysi', 'Lilo & Roli', 'SF winners');
        $c->setDate(2025, 'final', 'Bea & Gysi', 'Lilo & Roli', '2025-09-08', '14:00', $open, 'chat L 07.09.25 Lilo "Morn am 2i. Final: Gysi /Bea : Roli / Lilo"; 08.09 "König Roli … Dä chlinär" (L&R won)');
    },
];
