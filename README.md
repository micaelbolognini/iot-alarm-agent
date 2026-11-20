# IoT Alarm Agent

Övningsprojekt: en AI-agent som bedömer larm från industrimaskiner.
Utifrån en maskin och en felkod tittar den på felkoden och de senaste
mätvärdena och returnerar en strukturerad bedömning: allvarlighetsgrad,
rekommenderad åtgärd, vad som saknas och om larmet ska eskaleras till tekniker.

Byggt med Laravel 13 och det officiella paketet `laravel/ai`. Fungerar med en
lokal modell (Ollama) eller Claude.

## Så fungerar det

```
alarm:assess "Press 1" E101
        │
        ├─ 1. koden hämtar underlaget (deterministiskt)
        │      LookupErrorCode      → beskrivning, allvarlighetsgrad, åtgärd
        │      GetRecentReadings    → mätvärden, trend, gränsvärde, marginal
        │
        ├─ 2. AlarmAgent bedömer underlaget (LLM, strukturerad output)
        │      bedomning · allvarlighetsgrad · atgard · saknas · eskalera
        │
        └─ 3. koden agerar på det strukturerade svaret
               skriver ut bedömningen och flaggar eskalering
```

Datan hämtas av koden och inte av modellen, så modellen behöver aldrig gissa.
Agenten exponerar också båda uppslagen som verktyg (`HasTools`) för
konversationell användning.

## Installation

Krav: PHP 8.4+, Composer, SQLite (standard) samt Ollama eller en Anthropic-nyckel.

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
```

Val av leverantör:

- **Ollama** (standard för `alarm:assess`, via `--provider`): kör
  `ollama pull qwen3:8b` och se till att Ollama körs på `localhost:11434`.
- **Claude**: sätt `ANTHROPIC_API_KEY` i `.env`.

Standardleverantören i `config/ai.php` är `openai`. Kommandot åsidosätter den
med `--provider`.

## Användning

```bash
php artisan alarm:assess "Press 1" E101
php artisan alarm:assess "Kompressor 2" W204 --provider=anthropic
```

Demodata: `Press 1` har stigande temperatur och `Kompressor 2` har stabil
vibration. Felkoderna är `E101` (critical), `W204` (warning) och `I001` (info).

## Loggning

Varje uppslag som agentens verktyg gör loggas till `storage/logs/agent-*.log`
(kanalen `agent`), med felkod eller maskin och antal träffar.

## Tester

```bash
php artisan test   # bara testerna
composer test      # Pint, PHPStan och testerna, samma som CI
```

Agenten fakas med `AlarmAgent::fake()`, så testerna behöver varken modell eller
nätverk. Verktygen testas mot en databas i minnet via factories.

## Projektstruktur

| Sökväg | Innehåll |
|--------|----------|
| `app/Ai/Agents/AlarmAgent.php` | Instruktioner, verktyg och outputschema |
| `app/Ai/Tools/` | `LookupErrorCode`, `GetRecentReadings` |
| `app/Console/Commands/AssessAlarm.php` | Kommandot `alarm:assess` |
| `app/Models/` | `Machine`, `Reading`, `ErrorCode` |
| `config/ai.php` | Leverantörer (Ollama, Anthropic, ...) |

## Lärdomar

- **Strukturerad och deterministisk data krävdes för att Ollama skulle ge ett
  detaljerat svar.** Först fick modellen själv anropa verktygen. Det räckte
  inte för att Ollama skulle ge ett detaljerat svar om maxtemperatur och
  marginal till gränsen. När koden hämtade datan deterministiskt och skickade
  in den strukturerat fungerade det, både med Ollama och Claude.
- **Strukturerad output gör svaret testbart.** Koden agerar på fält som
  `eskalera` i stället för att tolka fri text.
- **Fejka agenten i tester.** `AlarmAgent::fake()` ger snabba tester utan modell
  eller nätverk. Själva promptens kvalitet testas inte så, den kräver separata
  utvärderingar mot en riktig modell.
