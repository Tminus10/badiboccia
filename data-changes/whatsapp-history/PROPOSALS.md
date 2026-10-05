# WhatsApp history import — proposed DEV changes, per year

Sources
- **Chat L** – export from Lilo's iPhone (`_chat.txt`, 28.08.2018 – 27.09.2026, 516 photos).
- **Chat P** – export from Paschi's iPhone (24.04.2018 creation msg, then 2022 – 2026, 142 photos, 23 not in L).
  Adds only 4 extra messages to L; no extra results.
- **Photos** – group draws, printed group plans with handwritten results, bracket sheets (file numbers given as `#NNNN`).
- Chat gaps: nothing before 28.08.2018 (2018 group phase), and 11.09.2023 – 03.01.2024 (disappearing messages were on).

Baseline: DEV = copy of PROD taken 01.10.2026 23:29 (`~/backups/badiBocciaProd-for-dev-copy-20261001-232927.sql`).
Game `#id`s below refer to that baseline.

Legend: ✅ DEV already agrees · ✏️ change proposed · ➕ new data · ❓ needs your decision

---

## 2018 — season exists, only finals day is in the chat

| | Game | DEV | Proposal | Evidence |
|---|---|---|---|---|
| ➕ | #128 Final Stella & Patrik 1:2 Jöggu & Puppe | no date | **2018-09-09, 16:00** | Debi 06.09 "Finale so ca. ab 16.00"; 18:04 "1:1 … gahd sicher nu 45 Min"; Puppe 18:59 "Sieg". Result ✅ |
| ➕ | #213 3rd place Tubä & Wurm 0:2 Debi & Städi | no date | **2018-09-09, 13:00** | "erschtä Match … 13h". Result not verifiable |

Nothing else for 2018 (group phase and QF/SF predate the chat).

---

## 2019 — ➕ season missing in DEV (full tournament found)

Rules (printed plans #0287): sets to 15, best of 3, 1st+2nd to QF, group games until ~31 July, KO to 21.
Draw 11.06.2019 (#0245, #0287). Final 01.09.2019.

**New team:** `Sandra & Matthias` (Sandra / Matthias). All other 15 teams already exist.

| Group | Teams (existing team ids) |
|---|---|
| A | Frei & Frei (8), Odi & Stierli (36), Georges & Peter (32), Debi & Städi (34) |
| B | Zimi & Roman (15), Bruno & Werni (30)¹, Lilo & Roli (10), Stella & Patrik (31) |
| C | Andrea & Armin (16), Moni & Erwin (17), Sandra & Matthias (new), Tubä & Wurm (11) |
| D | Bea & Gysi (7), Erika & Christine (29), Maja & Paschi (13), Jöggu & Puppe (4) |

¹ chat later says "Bruno und Beat" — Beat probably replaced Werni mid-season; kept as Bruno & Werni.

Group games (24 fixtures created by the app; known data filled in):

| Grp | Game | Date / time | Result | Evidence |
|---|---|---|---|---|
| A | Frei & Frei – Odi & Stierli | – | 2:0 | plan #0394 |
| A | Frei & Frei – Georges & Peter | – | 0:2 | plan #0394 |
| A | Odi & Stierli – Georges & Peter | – | 2:0 | plan #0394 |
| A | Odi & Stierli – Debi & Städi | 2019-07-13 14:00 | 2:0 | chat 13.07 + plan #0394 |
| A | Debi & Städi – Frei & Frei | 2019-07-27 14:00 | – | chat |
| A | Debi & Städi – Georges & Peter | 2019-08-03 14:00 | – | chat |
| B | Lilo & Roli – Stella & Patrik | 2019-07-20 14:00 | – | chat |
| B | Stella & Patrik – Zimi & Roman | 2019-07-24 19:00 | – | chat |
| B | Lilo & Roli – Bruno & Werni | 2019-08-01 (after 14:00 game) | – | chat |
| B | Stella & Patrik – Bruno & Werni | 2019-08-03 16:00 | – | chat |
| C | Moni & Erwin – Tubä & Wurm | 2019-06-26 | 0:2 | plan #0404 |
| C | Andrea & Armin – Tubä & Wurm | ≤ 2019-07-13 | 2:0 | plan #0404 + chat |
| C | Sandra & Matthias – Tubä & Wurm | 2019-07-16 18:30 | – | chat |
| C | Moni & Erwin – Andrea & Armin | 2019-07-18 19:15 | – | chat |
| C | Andrea & Armin – Sandra & Matthias | 2019-07-22 18:30 | – | chat |
| C | Moni & Erwin – Sandra & Matthias | 2019-07-23 19:15 | – | chat ("Miär sind dussä") |
| D | Jöggu & Puppe – Erika & Christine | 2019-07-10 18:30 | – | chat |
| D | Maja & Paschi – Bea & Gysi | 2019-07-15 19:00 | – | chat |
| D | Jöggu & Puppe – Maja & Paschi | 2019-07-17 19:00 | – | chat |
| D | Erika & Christine – Bea & Gysi | 2019-07-20 16:00 | 0:2 | chat "Hend wieder verlore… 2:0" |
| D | Bea & Gysi – Jöggu & Puppe | 2019-08-01 14:00 | – | chat |
| D | Maja & Paschi – Erika & Christine | 2019-08-07 19:00 | – | chat |

KO (bracket sheets #0552, #0631 — same crossed seeding the app uses):

| Slot | Game | Date / time | Result |
|---|---|---|---|
| QF1 | Odi & Stierli – Stella & Patrik | 2019-08-12 18:00 | 2:1 |
| QF2 | Andrea & Armin – Bea & Gysi | 2019-08-15 11:00 | 1:2 |
| QF3 | Lilo & Roli – Frei & Frei | 2019-08-17 ~11:00 | 2:0 |
| QF4 | Jöggu & Puppe – Tubä & Wurm | 2019-08-13 ~18:00 | 2:0 |
| SF1 | Odi & Stierli – Bea & Gysi | 2019-08-19 16:00 | 2:0 |
| SF2 | Lilo & Roli – Jöggu & Puppe | 2019-08-24 14:00 | ❓ J&P won, score unknown |
| 3rd | Bea & Gysi – Lilo & Roli | 2019-09-01 11:00 | ❓ Bea & Gysi won, score unknown |
| Final | Odi & Stierli – Jöggu & Puppe | 2019-09-01 14:00 | ❓ J&P won (👑, "Krönung"), score unknown |

⚠️ With 7 of 24 group results known, the 2019 group tables in the app will not reproduce the real top 2.

---

## 2020 — season exists (groups ✅ per draw #0941; group B results ✅ per plan #1128)

| | Game | DEV | Proposal | Evidence |
|---|---|---|---|---|
| ✏️ | #183 QF Jöggu & Puppe – Paschi & Patrik | **2:1** | **2:0** | Lilo 15.08: "21:19/21:13" |
| ➕ | #182 QF Erika & Debi – Bea & Gysi | – | 2020-08-09 | chat 04./05.08 (after the two 11:00 games) |
| ➕ | #184 QF Odi & Stierli – Sandra & Werni | – | 2020-08-09 11:00 | chat |
| ➕ | #185 QF Lilo & Roli – Tubä & Wurm | – | 2020-08-09 (after 11:00 game) | chat |
| ➕ | #180 SF Erika & Debi – Jöggu & Puppe | – | 2020-08-16 14:00 ❓ | Puppe "Sonntag 14.00"; Stella 18.08 says "gestern" (=17.08) |
| ➕ | #181 SF Odi & Stierli – Tubä & Wurm | – | 2020-08-20 17:00 | chat |
| ➕ | #214 3rd Jöggu & Puppe – Odi & Stierli | – | 2020-08-23 11:00 | chat ("stierli/walti hend gwunnä" ✅) |
| ➕ | #179 Final Erika & Debi – Tubä & Wurm | – | 2020-08-23 (after 3rd, ~13:00) | chat ✅ champions |
| ℹ️ | #205 Frei & Frei – Paschi & Patrik 1:1 | 1:1 | keep | 02.08 "machen erneuten Versuch" = unfinished; current code scores 1:1 as 1 pt each, so the table now matches the bracket |

Group dates (results ✅ unchanged):
#189 2020-06-21 ~13:30 · #194 2020-06-27 ~14:30 (15:9 5:15 15:13) · #197 2020-06-28 12:00 · #186 2020-07-04 13:30 ·
#203 2020-07-05 14:00 · #192 2020-07-06 ~14:00 (15:14 15:13) · #196 2020-07-07 (15:5 6:15 15:7) · #204 2020-07-07 18:30 ·
#190 2020-07-11 13:30 · #207 2020-07-11 (after) · #202 2020-07-12 11:30 · #201 2020-07-14 18:30 · #199 2020-07-17 18:30 (15:9 15:9) ·
#193 2020-07-18 14:00 · #200 2020-07-18 (after) · #187 2020-07-19 14:00 · #198 2020-07-20 18:30 · #208 2020-07-21 18:30 ·
#188 2020-07-23 18:45 · #206 2020-08-05 18:30 · #205 2020-08-05 (retry, after). (#209 ✅ already dated.)

---

## 2021 — season exists (groups ✅ per draw #1591); KO results missing

| | Game | DEV | Proposal | Evidence |
|---|---|---|---|---|
| ❓ | #222 QF Lilo & Roli – **Bea & Gysi** | Bea & Gysi | **Andrea & Armin** | Lilo 26.08: "Morn am houbi 6i. Joli Armin / Lilo Roli" (27.08, exactly in the QF week). But DEV group D results give Bea 5 pts vs A&A 4 — either a group result or the QF pairing in DEV is wrong. |
| ➕ | #221 QF Erika & Debi – Dani & Peter | – | 2021-09-03 17:30, Erika & Debi won | Debi 31.08 "…den Viertelfinale" |
| ➕ | #222 QF | – | 2021-08-27 17:30, Lilo & Roli won | |
| ➕ | #223 QF Stella & Patrik – Jöggu & Puppe | – | 2021-09-04 09:30, Stella & Patrik won | "äs gid ai das Jahr ä Königin" |
| ➕ | #224 QF Frei & Frei – Sandra & Werni | – | 2021-09-01 (~18:30), Sandra & Werni won | |
| ➕ | #218 SF Erika & Debi – Lilo & Roli | – | 2021-09-04 13:30, Erika & Debi won | |
| ➕ | #219 SF Stella & Patrik – Sandra & Werni | – | 2021-09-23 17:30, Sandra & Werni won | |
| ➕ | #220 3rd Stella & Patrik – Lilo & Roli | – | 2021-09-26 11:00, winner unknown | |
| ➕ | #217 Final Erika & Debi – Sandra & Werni | – | 2021-09-26 13:30, Erika & Debi won | "Königin und König bleiben" |

All KO set scores are unknown → see cross-year decision **D1**.

Group dates (results ✅ unchanged):
#250 2021-06-27 ~15:00 · #234 2021-07-03 13:30 · #256 2021-07-07 17:30 · #243 2021-07-10 ~15:00 · #237 2021-07-29 19:00 ·
#233 2021-07-31 11:00 · #254 2021-07-31 12:30 · #229 2021-07-31 14:00 · #240 2021-08-02 19:00 · #242 2021-08-06 16:00 ·
#228 2021-08-06 ~18:15 · #239 2021-08-11 19:00 · #226 2021-08-12 18:30 · #255 2021-08-13 15:30 · #227 2021-08-17 18:30 ·
#232 2021-08-18 18:30 · #241 2021-08-19 18:00 · #252 2021-08-19 19:15 · #248 2021-08-20 15:00 · #249 2021-08-21 11:00 (after rain-outs 04.07/09.08) ·
#247 2021-08-21 13:00 · #238 2021-08-23 18:30 · #225 2021-08-25 18:30 · #244 2021-08-30 18:00 (last set; started 14.08) · #230 2021-08-31 18:00.

---

## 2022 — ➕ season missing in DEV (full tournament found)

Draw 15.05.2022 (#2074). 19 teams. **New team:** `Caroline & Marius`. "Pädi & Kusi" → existing team #5 (see **D2**).

| Group | Teams |
|---|---|
| A | Caroline & Marius (new), Henry & Husi (9), Evelyne & Sven (2), Dani & Peter (18), Zimi & Roman (15) |
| B | Andrea & Armin (16), Tubä & Wurm (11), Bruno & Werni (30), Frei & Frei (8), Maja & Paschi (13) |
| C | Sandra & Stella (14), Bruni & Tiz (19), Pädi & Kusi (5), Moni & Erwin (17) |
| D | Erika & Debi (12), Jöggu & Puppe (4), Lilo & Roli (10), Bea & Gysi (7), Georges & Stierli (3) |

Group games (36 fixtures; 1 result known, the rest dates only):
A: Henry & Husi – Caroline & Marius 2022-06-27 18:00 · Evelyne & Sven – Caroline & Marius 2022-07-05 18:15 · Henry & Husi – Evelyne & Sven 2022-07-05 (after) ·
Henry & Husi – Dani & Peter 2022-07-06 ~19:00 · Evelyne & Sven – Zimi & Roman 2022-07-13 18:30 · Dani & Peter – Caroline & Marius 2022-07-15 18:15 ·
Evelyne & Sven – Dani & Peter 2022-07-17 09:15 · Zimi & Roman – Dani & Peter 2022-07-18 18:30 · Zimi & Roman – Caroline & Marius 2022-07-22 18:30 (proposed) · Henry & Husi – Zimi & Roman 2022-07-26 18:30
B: Tubä & Wurm – Andrea & Armin 2022-06-23 18:00 · Maja & Paschi – Tubä & Wurm 2022-06-26 16:30 · Tubä & Wurm – Frei & Frei 2022-07-06 18:30 ·
**Maja & Paschi – Frei & Frei 2022-07-09 09:30, Frei won (score ❓)** · Andrea & Armin – Frei & Frei 2022-07-12 18:00 · Tubä & Wurm – Bruno & Werni 2022-07-14 18:00 ·
Frei & Frei – Bruno & Werni 2022-07-21 18:00 · Andrea & Armin – Maja & Paschi 2022-08-01 09:30 · Bruno & Werni – Maja & Paschi 2022-08-03 16:30
C: Sandra & Stella – Moni & Erwin 2022-06-25 09:30 · Pädi & Kusi – Moni & Erwin 2022-06-26 12:00 · Bruni & Tiz – Sandra & Stella 2022-06-29 18:30 ·
Moni & Erwin – Bruni & Tiz 2022-06-30 18:30 · Sandra & Stella – Pädi & Kusi 2022-07-02 11:00 · Bruni & Tiz – Pädi & Kusi 2022-07-04 18:30
D: Erika & Debi – Jöggu & Puppe 2022-06-18 14:00 · Erika & Debi – Bea & Gysi 2022-06-26 10:00 · Jöggu & Puppe – Bea & Gysi 2022-07-12 19:30 · Bea & Gysi – Georges & Stierli 2022-07-13 10:00

KO (seeding was not the app's crossed scheme; slots arranged so the real semis come out):

| Slot | Game | Date / time | Result |
|---|---|---|---|
| QF1 | Lilo & Roli – Pädi & Kusi | 2022-08-10 18:00 | L&R won ❓ |
| QF2 | Tubä & Wurm – Zimi & Roman | 2022-08-09 18:30 | T&W won ❓ |
| QF3 | Erika & Debi – Frei & Frei | 2022-08-21 13:00 | E&D won ❓ |
| QF4 | Dani & Peter – Sandra & Stella | 2022-08-27 13:00 | D&P won ❓ |
| SF1 | Lilo & Roli – Tubä & Wurm | 2022-08-28 13:00 | **2:0** (live ticker) |
| SF2 | Erika & Debi – Dani & Peter | 2022-08-28 (after SF1) | **2:1** |
| 3rd | Dani & Peter – Tubä & Wurm | 2022-09-10 10:00 | **2:1** (20:21, 21:13, …) |
| Final | Erika & Debi – Lilo & Roli | 2022-09-11 ~13:25 | **2:1** (21:20, 6:21, …) |

---

## 2023 — season exists (groups ✅ per draw #2476/plan #2492)

| | Game | DEV | Proposal | Evidence |
|---|---|---|---|---|
| ✏️ | #8 Atti & Sarah 0:2 Evelyne & Sven | **2023-11-06** 17:00 | **2023-06-11** 17:00 | plan #2492 "11. Juni / 17.00 · 2:0" — day/month swapped |
| ✏️ | #29 Erika & Debi 2:0 Sandra & Stella | **2023-11-06** 14:00 | **2023-06-11** 14:00 | plan #2492 "11. Juni 14.00 · 2:0" |
| ➕ | #18 Bea & Gysi 2:1 Frei & Frei | no date | 2023-06-08 17:00 | plan #2492 |

Group dates (same as the earlier "section C" import that the PROD→DEV copy wiped):
A: #15 06-26 18:30 · #12 06-28 19:00 · #13 07-05 19:00 · #10 07-09 14:00 · #9 07-15 10:00 · #16 07-19 19:00 · #17 07-20 19:00 · #14 08-02 18:30 · #11 08-05 09:00
B: #24 06-14 17:00 · #21 06-15 18:30 · #27 07-08 11:00 · #20 07-10 18:00 · #19 07-20 10:30 · #25 07-22 10:00 · #26 07-23 09:30 · #23 08-16 17:00
C: #28 06-18 10:00 · #30 07-04 19:00 · (#31 ✅) · #33 07-23 14:00 · #32 07-25 18:00
D: #34 07-02 10:30 · #39 07-06 18:00 · #37 07-13 18:00 · #36 07-18 18:15 · #38 07-21 18:15 · #35 07-22 13:00

KO (slots arranged so the real semis come out):

| Slot | Game | Date / time | Result |
|---|---|---|---|
| QF1 | Lilo & Roli – Pädi & Kusi | 2023-08-24 18:30 | L&R won ❓ |
| QF2 | Erika & Debi – Bruni & Tiz | 2023-08-14 17:00 | E&D won ❓ |
| QF3 | Georges & Stierli – Moni & Erwin | 2023-08-13 10:00 | G&S won ❓ |
| QF4 | Tubä & Wurm – Zimi & Roman | 2023-08-22 18:30 | T&W won ❓ |
| SF1 | Lilo & Roli – Erika & Debi | 2023-08-26 13:15 | L&R won ❓ |
| SF2 | Georges & Stierli – Tubä & Wurm | 2023-08-25 17:00 | G&S won ❓ |
| 3rd | Erika & Debi – Tubä & Wurm | 2023-09-10 10:00 | unknown |
| Final | Lilo & Roli – Georges & Stierli | 2023-09-10 11:00 | L&R won (probably 2:0: 1st set 11:52, congratulations 12:34) ❓ |

Group results for 2023 are still mostly missing (no later plan photo exists).

---

## 2024 — season exists (groups ✅ per draw #3000; all group results present)

| | Game | DEV | Proposal | Evidence |
|---|---|---|---|---|
| ✏️ | #57 Maja & Paschi 2:1 Siri & Peter | 2024-08-05 18:30 | **2024-08-13 17:00** | started 30.06 16:30, 3rd set played Tue 13.08 17:00 ("Dienstag um fünf"); 05.08 was the Zimi game |
| ✏️ | #70 Jöggu & Puppe 2:1 Kusi W. & Patrik | 2024-07-17 | **2024-07-30 18:00** | Jöggu 26/27.07: "unterbrochnig Match fertig zuspielä … Zeyschtig 30.7. 18:00" |
| ℹ️ | #40 Bea & Gysi 2:1 Guli & Odi (26.05) | | keep | a group-A **decider** Bea/Gysi v Guli/Odi was played 27.07 10:00 — the app has no tiebreak games (**D4**) |

Times to add (dates ✅): #41 09:30 · #43 16:00 · #46 16:00 · #45 15:00 · #47 18:00 · #44 13:30 · #42 18:00 · #48 14:00 · #49 18:00 ·
#54 17:00 · #50 19:00 · #56 19:00 · #53 ~19:30 (after 18:00 game) · #65 18:00 · #62 18:00 · #60 18:00 · #61 18:00 · #63 13:00 ·
#69 13:00 · #67 18:30 · #71 18:00 · #73 18:00 · #66 18:00 · #68 18:00 · #72 17:00 · #75 14:00

KO (all empty in DEV):

| Slot | Game | Date / time | Result |
|---|---|---|---|
| QF1 | Erika & Debi – Henry & Husi | 2024-08-09 18:00 | E&D won ❓ |
| QF2 | Tubä & Wurm – Georges & Stierli | 2024-08-11 11:00 (07.08 flooded) | T&W won ❓ |
| QF3 | Bea & Gysi – Lilo & Roli | 2024-08-03 13:00 | B&G won ❓ |
| QF4 | Evelyne & Sven – Sandra & Werni | 2024-08-14 09:30 | E&S won ❓ |
| SF1 | Erika & Debi – Tubä & Wurm | 2024-08-15 10:30 | E&D won ❓ |
| SF2 | Bea & Gysi – Evelyne & Sven | 2024-08-20 17:30 | E&S won ❓ |
| 3rd | Tubä & Wurm – Bea & Gysi | 2024-09-08 10:00 | T&W won ❓ |
| Final | Erika & Debi – Evelyne & Sven | 2024-09-08 (after 3rd) | E&D won ❓ |

---

## 2025 — season exists (groups ✅ per draw #3617), but **no results at all** in DEV

Draw photo writes "Pädi/Philipp" where DEV has `Patrick & Philipp` (Patrick + Philipp Frei) — same team, no change.

Dates/times to add (results unknown — no plan photo with results exists):
A: #81 06-09 13:30 · #85 06-11 18:00 · #84 06-12 18:00 · #76 06-21 10:00 · #82 06-27 18:30 · #83 06-30 19:30 · #87 07-02 19:00 · #80 07-05 10:00 ·
   #78 07-10 19:00 · #88 07-13 11:00 · #86 07-14 18:00 · #90 07-20 11:00 · #77 07-22 18:30 · #79 08-08 19:00
B: #96 06-09 10:00 · #98 06-24 18:00 · #92 06-25 10:00 · #95 06-26 10:00 · #97 07-01 18:30 · #94 07-15 18:30 · #99 07-17 18:30 · #93 07-19 10:00 · #91 07-23 16:00 · #100 08-06 19:00
C: #107 06-08 11:00 · #109 06-19 11:00 · #101 06-19 13:00 · #105 07-03 18:30 · #102 07-16 18:00 · #106 07-29 19:00 · #103 08-15 09:00 (Evelyne subbed for Atti) · #108 08-28 19:00 ("Entscheidungsspiel")
D: #118 06-16 18:00 · #119 06-17 18:00 · #120 06-19 16:00 · #117 06-20 10:00 · #111 06-22 10:00 · #115 06-29 10:00 · #113 07-14 19:30 · #116 07-30 18:00

KO (only partly known):
- Final **Lilo & Roli beat Bea & Gysi**, 2025-09-08 14:00 ("König Roli dä chlinär").
- SF Lilo & Roli – Jöggu & Puppe, 2025-09-03 17:45 → Lilo & Roli.
- Bea & Gysi – Sandra & Werni, 2025-08-24 11:00 → Bea & Gysi (QF or SF ❓).
- Jöggu & Puppe's QF vs. group-C team, set for 2025-08-30 13:00; opponent unclear (Patrick & Philipp could not find a date).
- 3rd place Jöggu & Puppe – Sandra & Werni, 2025-09-09 17:30, winner unknown.
→ Proposal: enter final + the SF/3rd pairings with dates; leave QFs empty unless you know them.

---

## 2026 — season exists and is complete ✅

All 40 group results in DEV match the plan photo #4145 (31.07.2026) and the KO results match the chat. Only dates/times to add:
A: #269 05-30 10:00 · #265 06-12 18:00 · #267 06-17 18:00 · #266 06-21 10:30 · #272 06-24 18:00 · #270 06-27 ~09:30 · #274 08-05 18:30 (+ times #271/#268/#273 18:30)
B: #275 06-12 09:30 · #276 07-02 ~19:00 · #280 07-06 10:30 · #279 07-07 19:00 · #281 07-11 11:30 · #283 08-06 19:00 (+ times #284 10:00, #277 11:00, #282 10:00, #278 18:00)
C: #290 06-15 18:00 · #294 06-18 18:00 · #293 06-22 18:30 · #292 06-25 18:00 · #291 06-27 08:30 · #287 07-02 18:00 · #289 07-08 18:00 · #288 07-15 18:30 · #286 07-20 18:00 (+ time #285 18:30)
D: #298 06-14 17:00 · #301 06-28 11:00 · #299 06-28 ~12:30 · #304 06-28 17:00 · #295 07-04 09:30 · #296 07-09 18:00 · #302 07-19 17:00 · #303 07-29 18:00 · #300 08-03 18:00 · #297 08-04 18:00
KO: #264 08-09 17:00 · #261 08-16 18:00 · #262 08-18 16:00 · #263 08-18 18:30 · #259 08-19 13:30 · #258 08-22 10:00 (#260/#257 ✅)
ℹ️ Group-B decider Erwin & Stierli – Evelyne & Sven, 11.08 19:00 (1 set to 15) — not representable (**D4**).

---

## Cross-year decisions

- **D1 – KO games where only the winner is known.** The app needs set scores to advance a team and to show the podium.
  Options: (a) leave empty (bracket shows pairings only, no champion); (b) enter 2:0 for the winner and mark it as estimated (needs a small schema addition, see plan).
- **D2 – duplicate team.** ✅ Done 2026-10-05: merged into #6 "Kusi W. & Pädi" (changeset `data-changes/teams/merge-kusi-w-paedi.php`, apply on PROD after all whatsapp-history changesets). Original note: #5 "Pädi & Kusi" (2023, 2025) and #6 "Kusi W. & Patrik" (2024, 2026) are the same pair: the chat calls them "Kusi & Pädi" every year 2022–2026 (Patrick Felber + Kusi). Proposal: merge into one team (keep #6's PIN/photo? your call) and use it for 2022 too.
- **D3 – "Patrik" naming.** Patrick Felber ("Pädi") appears as "Patrik" in #31 Stella & Patrik, #39 Paschi & Patrik and #6. No change proposed, just FYI.
- **D4 – tiebreak/decider games** (2024 group A, 2026 group B). Not representable today; the bracket already has the right qualifier.

---

## How the changes will be recorded (so they can be replayed on PROD)

1. One **changeset file per year** in this folder (`2019.php`, `2020.php`, …), written only after you approve that year.
   Each changeset addresses games by **season year + phase + team names**, never by DEV ids, so it applies to PROD
   even if auto-increment ids have drifted there. It reuses the app's own models (`Season::create`, `Team::create`,
   `Game::createGroupFixtures`, `Game::ensureBracketSkeleton`) and checks the *expected old value* before every update —
   if PROD differs from what DEV had, that row is skipped and reported instead of overwritten.
2. A runner `scripts/apply-changeset.php <file> [--dry-run]` executes a changeset in one transaction on the server
   (`ssh hostpoint-badiboccia … php`), after a `mysqldump` backup.
3. Migration `003_data_changesets.sql` adds a table `data_changesets (name, applied_at, summary)`; the runner refuses
   to apply the same changeset twice, so DEV and PROD each know which years they have.
4. Every touched game also gets an `audit_log` row with `actor_label = 'WhatsApp-Import <year>'`.
5. To deploy: run the same files with `--dry-run` against PROD, review the report, then apply.

## Improvement plan

Data quality
- Ask players for the remaining set scores (KO 2019–2025, groups 2019/2022/2023/2025) — a printable "missing results" list per year can be generated from DEV.
- Merge the duplicate team (D2) and add a team-merge tool to the admin area for future cases.

App (small, in priority order)
1. `games.result_estimated` flag (+ note) so "winner known, score guessed" (D1) is stored honestly and shown with a marker.
2. Free-text `games.note` (e.g. "unterbrochen, 3. Satz am 13.08 gespielt", "Ersatz: Evelyne für Atti") — several cases above need it.
3. Optional `played_from` date for games split over several days (2021 #244, 2024 #57/#70).
4. Tiebreak/decider game type per group (2024 A, 2026 B), counted only for ranking.
5. Date sanity check on save: reject dates outside the season year / after the final (would have caught 2018 #136 and 2023 #8/#29).
6. "Bracket seeding": allow free QF pairing per season — 2022/2023 did not use the crossed scheme.

Process for future seasons
- Teams enter results themselves via PIN (already possible) — the WhatsApp "Live-Ticker" posts show the demand.
- Admin photo upload of the paper plan per group as evidence, attached to the season.
- The `.ics` feed already exists — promote it in the group chat so dates are entered in the app rather than only in WhatsApp.
