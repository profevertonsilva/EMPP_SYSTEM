# EMPP v2

Reconstrução do EMPP (Electrospun Membrane Property Predictor) a partir da especificação v2.1
(pacote "EMPP - interface", Prof. Murilo Innocentini). Vive nesta pasta do repositório, isolada
do sistema em produção (raiz), que não depende dela. O `.htaccess` da raiz bloqueia `/EMPP_V2/`
por HTTP: nada daqui é servido enquanto a v2 não tiver ponto de entrada próprio.

## Estado

1. **Modelo de filtração v2.1 e alertas**, em PHP, com testes.
2. **Banco de dados**: esquema próprio (banco separado do v1), migrações, gravação das simulações
   e importação dos dados do v1.

Ainda sem telas nem rotas.

```
src/Model/              modelo (sem banco)
  FiltrationModel.php   simulação (porte de emppSimulate): M1–M6, opções legacy e inertial
  SimulationInput.php   entrada validada (unidades do modelo de referência)
  SimulationResult.php  resultado; penetração é o valor primário (eficiência = 1 − pen)
  SimulationRun.php     modelo + alertas + classe EN 1822 + ePM de uma vez
  Alerts.php            13 alertas, níveis NOTE e CHECK (porte de emppAlerts)
  En1822.php            classes E10–U17 e projeção para a próxima classe
  Respirator.php        FFP1–3, N95/N99/N100 (critério de eficiência)
  Iso16890.php          ePM1, ePM2,5, ePM10 e grupo
  Presets.php           cenários de norma e de ar (os mesmos valores semeados no banco)
src/Database/           conexão (modo SQL estrito, UTC) e executor de migrações
src/Repository/         SimulationRepository: grava e lê simulações completas
src/Service/            StandardSimulation: mantém a simulação no cenário padrão de cada membrana
src/Import/             V1Importer: copia o v1 para a v2
bin/migrate.php         aplica as migrações
bin/import-v1.php       importa o v1
database/migrations/    esquema (0001) e presets (0002)
storage/                imagens e fotos enviadas (privado, fora do git)
reference/              modelo de referência em JS, sem alteração
```

## Banco de dados

Banco próprio (`empp_v2`), nunca o do v1. Requer MySQL 8.0.16+ ou MariaDB 10.5+.

```
cp .env.example .env            # e preencher
php bin/migrate.php --create    # cria o banco e as tabelas
php bin/import-v1.php           # copia o v1 (pode rodar de novo na virada)
```

| Tabela | Conteúdo |
|---|---|
| `users`, `countries` | contas (perfil incluso) e países |
| `membranes` | nome, espessura, tempo de deposição, densidade do polímero, ponteiro para a simulação padrão |
| `membrane_images` | várias por membrana: arquivo, escala, área analisada, ε, df, features de Haralick |
| `fiber_diameters` | diâmetros medidos em cada imagem (histograma) |
| `spinning_params` | tensão, vazão, distância, concentração; todos opcionais |
| `simulations` | entrada completa + versão e opções do modelo + todos os resultados + alertas + curva fracionária |
| `measurements`, `dp_velocity_points` | medidas de bancada (Validation) |
| `aerosol_presets`, `air_presets` | cenários de ensaio (configuração) |

Decisões:
- Grandezas físicas em `DOUBLE`, com a unidade no nome da coluna (`_um`, `_pa`, `_m_s`).
  A penetração chega a 1e-89, fora do alcance de `DECIMAL`.
- Guarda-se a **penetração**, não a eficiência. Porosidade sempre em fração (0–1).
- A simulação gravada é uma foto: tem a entrada completa, então reabre igual mesmo que a
  membrana mude depois, e o teste confirma que recalcular a partir dela dá o mesmo resultado bit a bit.
- Floats vão ao banco por `Connection::params()`: o PDO cortaria a 14 dígitos.
- Cada membrana tem uma simulação no **cenário padrão** (EN 1822, Ribeirão Preto) gravada e
  mantida por `StandardSimulation::refresh()`, chamada sempre que espessura ou imagens mudam.
  Cartões, Compare e correlação leem esse registro em vez de recalcular.

Importação do v1 (`V1Importer`), só leitura no v1 e tudo numa transação:

| v1 | v2 |
|---|---|
| `login` + `researcher` | `users` (hash da senha mantido; SHA-1 antigo vira bcrypt no 1º login da v2) |
| `research` | `membranes` + `spinning_params` (vazão mL/min, tensão kV, distância cm; zero vira vazio) |
| `research_results` + arquivo | `membrane_images` (porosidade % → fração) |
| `filter_simulation` mais recente | espessura (mm → µm) e df da imagem |
| `simulation` | não importada: veio das equações v1 (com o erro de densidade no termo inercial); a v2 recalcula |

Rodar de novo atualiza pelos ids do v1 (`legacy_*`), sem duplicar.

## Testes

```
composer install
composer test
```

- `FiltrationModelTest`: os casos publicados na seção 6 da especificação, conferidos com as casas
  decimais publicadas.
- `ReferenceParityTest`: escalares, as 100 linhas da curva, alertas (texto incluso), classe e ePM
  contra o JS de referência, com tolerância relativa 1e-9.
- `ClassificationTest`: limites das classes, exemplo da Membrana A do manual, respiradores, validação.
- `tests/Database/`: esquema, restrições, gravação exata das simulações, simulação padrão e
  importação do v1 (fixture em `tests/fixtures/v1/`). Usam `TEST_DB_NAME` e `TEST_V1_DB_NAME`,
  que são **apagados e recriados** a cada execução (o nome precisa terminar em `_test`). Sem `.env`,
  esses testes são pulados.

A fixture do modelo só deve ser regenerada se o modelo de referência mudar:

```
node tests/fixtures/generate-reference.js
```

## Pendências

- M3: conferir a forma da correção de escorregamento na referência original (Pich 1966 / Brown 1993).
- O alerta R1 (MPPS maior que a fibra) não ocorre com entradas realistas neste modelo (MPPS/df
  fica abaixo de 0,91 numa varredura ampla); é testado com resultado sintético.
- Login da v2: aceitar o SHA-1 antigo e trocar por bcrypt no primeiro acesso, como o v1 faz.
- Produção: confirmar a versão do MySQL/MariaDB e se a hospedagem permite o segundo banco.
