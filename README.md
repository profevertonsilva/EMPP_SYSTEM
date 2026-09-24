# EMPP — Electrospun Membrane Property Predictor

Plataforma web para pesquisadores que trabalham com **membranas de nanofibras produzidas por eletrofiação (electrospinning)**. A partir de uma micrografia de MEV (SEM) da membrana e dos parâmetros de processo, o EMPP:

1. extrai **características de textura de Haralick** da imagem;
2. **prevê a porosidade** da membrana com um modelo de machine learning;
3. **simula o desempenho de filtração** da membrana (eficiência, perda de carga, fator de qualidade, MPPS etc.);
4. consolida as imagens e os resultados num **dataset exportável** para retreinar o modelo.

A interface é em inglês; o código mistura inglês e português.

---

## Sumário

- [Stack e dependências](#stack-e-dependências)
- [Estrutura de pastas](#estrutura-de-pastas)
- [Arquitetura](#arquitetura)
- [Perfis de usuário e controle de acesso](#perfis-de-usuário-e-controle-de-acesso)
- [Funcionalidades](#funcionalidades)
  - [1. Autenticação e cadastro](#1-autenticação-e-cadastro)
  - [2. Conta do usuário](#2-conta-do-usuário)
  - [3. Dashboard do pesquisador](#3-dashboard-do-pesquisador)
  - [4. Novo estudo de pesquisa](#4-novo-estudo-de-pesquisa)
  - [5. Análise: Haralick + predição de porosidade](#5-análise-haralick--predição-de-porosidade)
  - [6. Simulação de desempenho de filtração](#6-simulação-de-desempenho-de-filtração)
  - [7. Administração](#7-administração)
  - [8. Dataset de imagens e exportação ZIP](#8-dataset-de-imagens-e-exportação-zip)
- [Proxy das APIs de análise](#proxy-das-apis-de-análise)
- [Banco de dados](#banco-de-dados)
- [Rotas](#rotas)
- [Instalação e configuração](#instalação-e-configuração)
- [Scripts utilitários de deploy](#scripts-utilitários-de-deploy)
- [Limitações e pontos de atenção](#limitações-e-pontos-de-atenção)

---

## Stack e dependências

| Camada | Tecnologia |
|---|---|
| Backend | PHP ≥ 7.0 (MVC próprio, sem framework de mercado) |
| Banco | MySQL/MariaDB via PDO |
| Servidor | Apache ou LiteSpeed com `mod_rewrite` |
| Frontend | Template **Sneat 1.0.0** (Bootstrap 5), jQuery, Chart.js 4 + plugin annotation, DataTables, SweetAlert2, Cropper.js, Select2, jQuery Mask, Font Awesome, Boxicons |
| Análise de imagem / ML | API externa (Haralick e porosidade) hospedada em `rapzap.com.br`, acessada via proxy |

Pacotes Composer ([composer.json](composer.json)):

| Pacote | Uso |
|---|---|
| `vlucas/phpdotenv` | Carrega o `.env` |
| `nelexa/zip` | Gera o ZIP de exportação do dataset |
| `phpmailer/phpmailer` | Envio de e-mail (declarado, ainda não usado no fluxo) |
| `firebase/php-jwt` | JWT (declarado, ainda não usado) |
| `dompdf/dompdf` | PDF (declarado, ainda não usado) |
| `php-ffmpeg/php-ffmpeg`, `smalot/pdfparser` | Declarados, sem uso atual |

Extensões PHP necessárias: `pdo_mysql`, `mbstring`, `gd`, `zip`, `curl`, `json`, `dom`, `fileinfo`, `iconv`.

---

## Estrutura de pastas

```
EMPP_SYSTEM/
├── index.php                 # Front controller: autoload, sessão, .env e roteador
├── .htaccess                 # Redireciona tudo que não é arquivo físico para index.php
├── api_proxy.php             # Proxy autenticado para as APIs de Haralick e porosidade
├── diag.php                  # Diagnóstico de deploy (protegido por token, autodestrutivo)
├── fix_perms.php             # Corrige permissões de arquivos após upload via FTP
├── composer.json / .lock     # Dependências PHP
├── .env / .env.example       # Configuração de ambiente
│
├── App/
│   ├── Route.php             # Rotas fixas em código + rotas carregadas da tabela `routes`
│   ├── DAO.php               # Classe abstrata base dos DAOs (herda a conexão PDO)
│   ├── Controller/
│   │   ├── LoginController.php          # Login, cadastro, logout
│   │   ├── AdministratorController.php  # Todo o restante (pesquisador + admin)
│   │   ├── CountryController.php
│   │   └── ErrorController.php          # 404
│   ├── DAO/                  # Acesso a dados (uma classe por tabela)
│   ├── Model/                # Entidades com __get/__set
│   └── View/
│       ├── dashboard.php     # Layout: header + menu (por perfil) + navbar + conteúdo + footer
│       ├── login/            # sign_in, sign_up, forgot_password
│       ├── administrator/    # Todas as telas do painel
│       └── includes/         # Partes do layout (auth/ e dashboard/)
│
├── vendor/
│   ├── FW/                   # Micro-framework próprio (Bootstrap, Router, Action, Connection)
│   └── ...                   # Pacotes Composer
│
├── resources/
│   ├── img/                  # Logos e ícone EMPP
│   └── dashboard/            # Assets do template Sneat + customizações EMPP
│       ├── assets/css/empp.css
│       ├── assets/js/empp-decimal.js    # Bloqueia vírgula decimal
│       ├── assets/js/empp-rows.js       # Linhas de tabela clicáveis
│       └── assets/img/
│           ├── research/                # Imagens SEM enviadas
│           ├── researcher/              # Fotos de perfil de pesquisadores
│           └── administrator/           # Fotos de perfil de administradores
│
└── Banco de Dados/
    ├── Complete_DB_Script.sql           # Script de criação (desatualizado — ver abaixo)
    └── *.brM3                           # Modelos lógico/conceitual (brModelo)
```

---

## Arquitetura

### Fluxo de uma requisição

```
Navegador ──► .htaccess ──► index.php
                              │  vendor/autoload.php
                              │  session_start()
                              │  Dotenv carrega .env em $_ENV
                              ▼
                        App\Route  (extends FW\Init\Boostrap)
                              │  initRoutes(): rotas fixas + SELECT * FROM routes WHERE status = 1
                              │  run(url): compara rota estática ou regex de rota dinâmica
                              ▼
                  App\Controller\<Controller>::<action>()
                              │  DAO ─► PDO ─► MySQL
                              │  $this->getView()->x = ...
                              ▼
                   render('<view>', 'dashboard')
                              │  App/View/dashboard.php (layout)
                              │  $this->content() ─► App/View/administrator/<view>.php
```

### Micro-framework `vendor/FW`

| Classe | Papel |
|---|---|
| `FW\Init\Boostrap` | Recebe o mapa de rotas e despacha. Rotas **estáticas** comparam o path exato; rotas **dinâmicas** (`is_dynamic = 1`) convertem `pattern` (ex.: `dashboard/administrator/researchers/{id}`) em regex com grupos nomeados, passados à action como `$matches`. Sem correspondência → `ErrorController::error404`. |
| `FW\Router\RouteManager` | Singleton que lê a tabela `routes` (`status = 1`). |
| `FW\Controller\Action` | Base dos controllers: objeto `view`, `render()`, `content()` e `getParams()` (segmentos da URL depois do slug; ex.: em `/dashboard/researcher/research/view/42`, `getParams()[1]` = `42`). |
| `FW\DB\Connection` | Abre o PDO MySQL (`charset=utf8`) a partir de `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`. |
| `FW\Controller\FuncoesGlobais` | Helpers: `popularModel()` (preenche um Model a partir de um array), conversão de datas, formatação de moeda e telefone. |

### Rotas híbridas

A maioria das rotas fica na tabela **`routes`** do banco (colunas `nome_rota`, `slug`, `controller`, `action`, `is_dynamic`, `pattern`, `status`). Algumas rotas novas foram colocadas direto em [App/Route.php](App/Route.php) para irem junto com o código:

- `/error404`
- `/dashboard/administrator/researchers/{id}` (dinâmica)
- `/dashboard/administrator/dataset`
- `/dashboard/administrator/dataset/download`
- `/dashboard/account/update-profile`

> Para criar uma tela nova, cadastre a rota na tabela `routes` **ou** adicione-a em `Route::initRoutes()`.

---

## Perfis de usuário e controle de acesso

O campo `login.log_type` define o perfil:

| Perfil | Código | Acesso |
|---|---|---|
| **Pesquisador** | `R` | Suas próprias pesquisas, simulações e conta. |
| **Administrador** | `A` | Tudo do pesquisador (o admin também é pesquisador) **+** a área administrativa: visão geral, pesquisadores, todas as pesquisas e dataset. |

Guardas em [AdministratorController.php](App/Controller/AdministratorController.php):

- `requireLogin()` — sem sessão, redireciona para `/sign-in`.
- `requireAdmin()` — exige `log_type = 'A'`; senão redireciona para `/dashboard/researcher`.
- `requireResearchAccess($research)` — um estudo só pode ser aberto pelo **dono** ou por um **administrador**. Isso vale para visualizar, gravar resultados, atualizar porosidade e criar/ver simulações.
- IDs sensíveis vêm da **sessão**, não do formulário: o dono de uma nova pesquisa, o perfil e a senha editados são sempre os do usuário logado.
- Um admin pode abrir o estudo de outra pessoa, mas os botões de processamento aparecem só para o dono (`is_owner`).

O menu lateral muda conforme o perfil (`menu_administrator.php` × `menu_researcher.php`).

---

## Funcionalidades

### 1. Autenticação e cadastro

**Controller:** `LoginController`

| Tela | Rota | Descrição |
|---|---|---|
| Login | `/sign-in` → POST `/signin` | E-mail e senha. Se a senha confere, renova o ID da sessão, grava `log_id`, `log_type`, `log_status` e `res_photo` e redireciona conforme o perfil (`/dashboard/administrator` ou `/dashboard/researcher`). E-mail inexistente e senha errada recebem a mesma mensagem (`?error=1`). |
| Cadastro | `/sign-up` → POST `/signup` | Cria um registro em `login` (perfil `R`) e outro em `researcher`. Campos: nome, instituição, grau acadêmico (Undergraduate Student, Bachelor, Master, PhD ou *Other* com texto livre), finalidade de uso, país (lista da tabela `country`, com busca via Select2), e-mail, senha e aceite da política de privacidade/termos (exibida em modal). |
| Esqueci a senha | `/forgot-password` | Só a tela. **O envio ainda não foi implementado** (o formulário aponta para `index.html`). |
| Logout | `/signout` | Destrói a sessão e volta ao login. |

### 2. Conta do usuário

Acessível a qualquer usuário logado pelo menu do avatar na navbar:

| Função | Rota | Detalhes |
|---|---|---|
| Meu perfil | `/dashboard/administrator/my-profile` → POST `/dashboard/account/update-profile` | Edita nome, instituição, grau acadêmico, finalidade e país. Sempre atualiza o perfil do usuário da sessão. |
| Minha senha | `/dashboard/administrator/my-password` → POST `/dashboard/administrator/update-my-password` | Exige a senha atual e a confirmação da nova. Retornos via `?success=`: `1` ok, `0` senha atual errada, `2` erro ao gravar, `3` campos vazios/confirmação diferente. |
| Minha foto | `/dashboard/administrator/my-photo` → POST `/dashboard/administrator/upload-photo` | Aceita JPEG, PNG, GIF ou WEBP. O arquivo recebe nome MD5 aleatório e vai para `img/administrator/` ou `img/researcher/` conforme o perfil. Depois `updateMyPhoto` grava `researcher.res_photo`. Sem foto, o sistema mostra a inicial do nome (`ResearcherModel::initial()`). |

### 3. Dashboard do pesquisador

**Rota:** `/dashboard/researcher` — `dashboardResearcher()`

- Boas-vindas com o nome do pesquisador.
- Atalho **New research study**.
- Lista dos estudos mais recentes **do próprio usuário**, com miniatura da imagem e porosidade prevista (quando já existe).

**Minhas pesquisas:** `/dashboard/researcher/research-studies` — tabela (DataTables: busca, ordenação e paginação) de todos os estudos do usuário. As linhas são clicáveis (`empp-rows.js`).

### 4. Novo estudo de pesquisa

É um assistente de dois passos, com indicador de progresso (`new_research_steps.php`):

**Passo 1 — Imagem SEM** (`/dashboard/researcher/research/new`)

- Upload de JPG, PNG ou WEBP de até 5 MB.
- Editor de recorte (**Cropper.js**) num modal, com *Apply crop* e *Reset crop*. A orientação da tela é recortar a barra de informações e a escala do microscópio, deixando só as fibras.
- POST `/dashboard/researcher/research/upload-file` (`upload_research.php`) valida tipo e extensão, salva em `resources/dashboard/assets/img/research/<md5>.jpg` e guarda o nome em `$_SESSION['ree_file']`.

**Passo 2 — Parâmetros de eletrofiação** (`/dashboard/researcher/research/info`)

| Campo | Coluna |
|---|---|
| Nome do estudo | `ree_name` |
| Descrição | `ree_description` |
| Vazão da seringa | `ree_flow` |
| Tensão | `ree_voltage` |
| Distância agulha–coletor | `ree_distance` |

- POST `/dashboard/researcher/research/insert` (`researchInsert`). Vazão, tensão e distância têm de ser **números positivos com ponto decimal**; se não forem, volta com `?error=number`.
- O estudo é gravado para o usuário da sessão, e o sistema redireciona para a página do estudo.

### 5. Análise: Haralick + predição de porosidade

**Tela do estudo:** `/dashboard/researcher/research/view/{id}` (`researchView`)

A página mostra a imagem SEM, os parâmetros de processo e um **pipeline de análise em 3 etapas**, cada uma com status *Done*, *Ready* ou *Pending*:

**Etapa 1 — Características de textura de Haralick**

O botão **Analyse image & predict porosity** roda, em sequência e no navegador:

1. `POST /api_proxy.php?target=haralick` com `{ image_url, image_id }`. A API devolve `haralick_features`: **Dissimilarity, Correlation, Energy e Homogeneity**.
2. `POST /dashboard/researcher/research/insertResult` grava as 4 features em `research_results`.
3. `POST /api_proxy.php?target=porosity` no formato MLflow `dataframe_split`:
   ```json
   {
     "dataframe_split": {
       "columns": ["Syringe_flow_rate", "Tension", "Distance",
                   "Dissimilarity", "Correlation", "Energy", "Homogeneity"],
       "data": [[flow, voltage, distance, diss, corr, energy, homog]]
     }
   }
   ```
4. `POST /dashboard/researcher/research/updateporosity` grava `rre_porosity`.

O progresso aparece na tela e as falhas em alertas SweetAlert2. Se as features forem salvas mas a predição falhar, a página recarregada mostra **Predict porosity** para repetir só essa etapa.

**Etapa 2 — Porosidade prevista**

É o resultado principal: um valor em %, com barra de escala de 0 a 100 % e animação.

**Etapa 3 — Simulação de filtração**

Liberada depois da porosidade: **Set up simulation**, ou **View simulation** se já houver uma. Vale sempre a simulação mais recente do estudo.

### 6. Simulação de desempenho de filtração

**Configuração:** `/dashboard/researcher/research/filter-simulation-create/{ree_id}` → POST `/dashboard/researcher/filterSimulationSave`

Campos de entrada:

| Grupo | Campo | Unidade |
|---|---|---|
| **Membrana** | Material | texto |
| | Porosidade ε (preenchida a partir da porosidade prevista ÷ 100) | fração 0–1 |
| | Espessura L | mm |
| | Diâmetro médio da fibra d<sub>f</sub> | µm |
| | Densidade da fibra ρ<sub>f</sub> | kg/m³ |
| | Área do filtro | m² |
| **Condições de operação** | Temperatura T | °C (aceita negativo) |
| | Pressão P | mmHg |
| | Velocidade de face do ar v<sub>s</sub> | m/s |
| **Aerossol** | Tamanho mínimo de partícula d<sub>pi,min</sub> | µm |
| | Tamanho máximo de partícula d<sub>pi,max</sub> | µm |
| | Concentração de pó na entrada C | mg/m³ |
| | Classes de tamanho | fixo em 100 |

Validação no servidor (redireciona com `?error=`):
- `number`: todos os campos numéricos precisam ser números com **ponto** decimal (`"0,7"` é rejeitado, não convertido);
- `porosity`: 0 < ε < 1;
- `range`: tamanhos, espessura, diâmetro, velocidade e pressão > 0, e d<sub>max</sub> > d<sub>min</sub>.

No cliente, `empp-decimal.js` bloqueia a vírgula no teclado, na colagem e no autofill, marca o campo como inválido e impede o envio do formulário. Assim `0,03` não vira `003`, um erro de 100×.

**Propriedades derivadas gravadas na tabela `simulation`** (`deriveSimulationProperties`):

| Propriedade | Fórmula |
|---|---|
| Fração sólida (α) | `1 − ε` |
| Permeabilidade k₁ | `d_f² / (64·α^1.5·(1 + 56·α³))` (d_f em m) |
| Permeabilidade k₂ | `exp(−1.71588 · k₁^−0.08093)` |
| Pressão de teste | `P/760 · 101325` Pa |
| Densidade do ar | `P_teste · 0.028965 / (8.314 · (T + 273))` |
| Viscosidade do ar (Sutherland) | `1.73e−5 · ((T+273)/273)^1.5 · 398/(T+398)` |
| Livre caminho médio | `21.2255 · μ · √(T+273) / P_teste` |
| Número de Kuwabara | `−ln(α)/2 − 3/4 + α − α²/4` |
| Número de Knudsen | `2λ / d_f` |
| Constante de Boltzmann e gravidade | 1.380649e−23 e 9.81 |

**Resultado:** `/dashboard/researcher/research/filter-simulation-view/{fis_id}`

O cálculo completo roda em PHP na view ([filter_simulation_view.php](App/View/administrator/filter_simulation_view.php)):

1. **Distribuição de tamanho de partícula (Rosin-Rammler / Weibull)** em 100 classes log-espaçadas entre d<sub>min</sub> e d<sub>max</sub>. Calcula o parâmetro de forma `a`, o diâmetro característico `d_m`, a fração mássica discreta e normalizada por classe, a fração acumulada e o **diâmetro de Sauter**.
2. **Eficiência de fibra única** por classe:
   - fator de escorregamento de Cunningham `Fs` e coeficiente de difusão browniana `D`;
   - número de Péclet `Pe`;
   - **difusão** η<sub>D</sub> = 1.6·(ε/Ku)^⅓·Pe^−⅔·C<sub>d</sub>;
   - **interceptação** η<sub>R</sub> = 0.6·(ε/Ku)·R²/(1+R)·C<sub>r</sub>, limitada a 1;
   - **impactação inercial** η<sub>I</sub> = Stk³/(Stk³ + 0.77·Stk² + 0.22);
   - total: η<sub>T</sub> = 1 − (1−η<sub>D</sub>)(1−η<sub>R</sub>)(1−η<sub>I</sub>).
3. **Eficiência fracionária (grade efficiency)**: `E_grade = 1 − exp(−4·η_T·α/(1−α)·L/d_f)`.
4. **Indicadores (KPIs)**:

| KPI | Cálculo |
|---|---|
| **Eficiência global** E<sub>overall</sub> | Σ E<sub>grade</sub>·W<sub>i</sub> (ponderada por massa), em % |
| **Perda de carga** ΔP | `(μ/k₁·v + ρ/k₂·v²) · L` (Forchheimer), em Pa |
| **Fator de qualidade** QF | `−ln(1 − E) / ΔP`, em Pa⁻¹ |
| **MPPS** | diâmetro da classe com menor E<sub>grade</sub>, em nm |
| **E<sub>min</sub>** | eficiência fracionária na MPPS |
| **Vazão** | `v · área · 1000 · 60` L/min |
| **Concentração na saída** | `C · (1 − E/100)` mg/m³ |
| **Contribuição viscosa × inercial** | % de ΔP vinda de cada termo |

5. **Interpretação**: cada KPI tem um veredito colorido, faixas de referência e um painel explicativo (*O que é*, *Como ler*, *Como o EMPP calcula*). As comparações usam padrões reais: N95 ≥ 95 %, FFP2 ≥ 94 %, FFP3 ≥ 99 %, HEPA ≥ 99,97 %, resistência respiratória de 240–350 Pa, a diretriz da OMS para PM<sub>2.5</sub> e vazões NIOSH/EN 149. A tela também avisa quando a MPPS cai na borda da faixa simulada ou quando o diâmetro de fibra informado é atípico para eletrofiação.
6. **Gráficos (Chart.js)**:
   - eficiência fracionária × frequência mássica;
   - eficiência de fibra única por mecanismo (difusão, interceptação, impactação e total);
   - distribuição de tamanho de partícula, com frequência mássica e acumulada em escala log e botão *Update chart*.
7. **Tabelas** com os valores intermediários por classe.

**Robustez com dados legados:** registros antigos gravados com vírgula decimal tiveram as propriedades derivadas calculadas como zero. Se as entradas continuam válidas, `FilterSimulationView` **recalcula** essas propriedades só para exibição, sem gravar nada. Se o domínio for inválido, a tela explica o motivo e oferece **Set up a new simulation**.

### 7. Administração

Disponível só para `log_type = 'A'`:

| Tela | Rota | Conteúdo |
|---|---|---|
| **Visão geral** | `/dashboard/administrator` | Totais de pesquisadores e de estudos, e os 8 estudos mais recentes da plataforma, com links para o autor e o estudo. |
| **Pesquisadores** | `/dashboard/administrator/researchers` | Tabela com nome, e-mail, perfil, país, instituição e nº de estudos, com links para ver ou editar. |
| **Detalhe do pesquisador** | `/dashboard/administrator/researchers/{log_id}` | Perfil completo, todos os estudos da pessoa e botão para baixar o dataset só dela. |
| **Editar pesquisador** | `/dashboard/administrator/researcher/edit/{log_id}` → POST `/dashboard/administrator/update-profile` | O admin edita o perfil de outra pessoa. |
| **Todas as pesquisas** | `/dashboard/administrator/research-studies` | Todos os estudos de todos os pesquisadores, com a porosidade mais recente. |
| **Dataset de imagens** | `/dashboard/administrator/dataset` | Ver seção abaixo. |

### 8. Dataset de imagens e exportação ZIP

**Tela:** `/dashboard/administrator/dataset` (`adminDataset`)

- Estatísticas: nº de estudos, imagens presentes no disco, estudos com features de Haralick e estudos com porosidade prevista.
- Os mesmos números agrupados **por pesquisador**, com download individual.

**Download:** `/dashboard/administrator/dataset/download[?researcher={log_id}]` (`adminDatasetDownload`)

Gera o arquivo `empp-dataset-AAAAMMDD[-nome-do-pesquisador].zip` com:

```
images/<ree_id>_<arquivo>.jpg   # imagens SEM (armazenadas sem recompressão)
dataset.csv                     # uma linha por estudo
README.txt                      # instruções para treino
```

As **13 primeiras colunas** de `dataset.csv` seguem o layout de `DatasetV3.csv`, lido pelo `mlops.py` da API Python (`nanofiber-porosity-api`). Assim, as linhas podem ser anexadas diretamente ao dataset de treino:

`File, Composição, Vazão da Seringa, Tensão, Distância, Rotação, Translação, Porosidade, Tamanho de Poro, Dissimilarity, Correlation, Energy, Homogeinity`

Depois vêm as colunas de proveniência: `Research ID, Study name, Researcher, Institution, Created at, Predicted porosity (EMPP model), Image included`.

> **Importante para treino:** a coluna `Porosidade` sai **vazia de propósito**. Ela deve receber a porosidade **medida** em laboratório. A porosidade prevista pelo modelo fica numa coluna separada, porque treinar com ela ensinaria o modelo a repetir as próprias previsões. Linhas sem features de Haralick precisam ser processadas antes.

---

## Proxy das APIs de análise

[api_proxy.php](api_proxy.php) é servido direto pelo Apache (não passa pelo roteador) e:

- exige **sessão autenticada**, para não virar uma ferramenta de SSRF aberta;
- aceita só `POST` com corpo JSON válido;
- usa uma **allowlist fixa**, em que o cliente escolhe um apelido e nunca uma URL:
  - `haralick` → `https://www.rapzap.com.br/api/haralick`
  - `porosity` → `https://www.rapzap.com.br/api/porosity`
- repassa a chamada via cURL servidor-a-servidor (timeout de 120 s, verificação SSL e sem seguir redirects), o que elimina o problema de CORS e esconde o endereço das APIs do JavaScript público;
- em caso de falha, responde `502` com mensagem genérica e grava o detalhe no `error_log`.

---

## Banco de dados

### Tabelas principais

```
country ──< researcher >── login ──< research ──< research_results
                                         │
                                         └──< filter_simulation ──< simulation
routes   (tabela de roteamento do framework)
```

| Tabela | Conteúdo |
|---|---|
| `login` | Credenciais: e-mail, senha (bcrypt via `password_hash`; hashes SHA-1 legados são aceitos e convertidos no login), status, tipo `R`/`A`, token e data de criação. |
| `researcher` | Perfil: nome, foto, instituição, finalidade, grau acadêmico, país e FK para `login`. |
| `country` | Lista de países (ISO2, ISO3, nome). |
| `research` | Estudo: nome, descrição, arquivo de imagem, vazão, tensão, distância (e as colunas não usadas composição, rotação e translação), data e FK do dono. |
| `research_results` | Features de Haralick e `rre_porosity`. Vale sempre o registro mais recente (`MAX(rre_id)`). |
| `filter_simulation` | Entradas da simulação. |
| `simulation` | Propriedades derivadas (e colunas reservadas para KPIs/distribuição). |
| `filter_distribution`, `distribution` | Previstas no modelo, sem uso atual. |
| `routes` | `nome_rota`, `slug`, `controller`, `action`, `is_dynamic`, `pattern`, `status`. |

Todas as FKs usam `ON DELETE CASCADE`.

> ⚠️ **O `Complete_DB_Script.sql` está desatualizado em relação ao código.** Antes de montar um ambiente novo, corrija:
> - nomes de colunas: `ree_descrition` → `ree_description`, `rre_homogeneinity` → `rre_homogeneity`, `fis_tickness` → `fis_thickness`;
> - colunas que faltam: `research_results.rre_porosity`, `filter_simulation.fis_class`, `simulation.sim_dm`, `sim_a`, `sim_sum_discrete`, `sim_sum_norm`, `sim_sum_dpi`, `sim_dsauter`, `sim_d50`, `sim_d90`, `sim_d10`, `sim_dpi`;
> - a tabela `routes`, que não existe no script;
> - valores `DEFAULT` para `log_status`, `log_confirmed`, `log_token` e `log_create` (`login`), para `ree_composition`, `ree_rotation`, `ree_translation` e `ree_create` (`research`), e para as colunas `sim_*` que o INSERT não preenche. Sem eles, os INSERTs do código falham em modo SQL estrito;
> - o `AUTO_INCREMENT` duplicado em `research_results.rre_id`.
>
> A fonte mais confiável do schema atual é um dump do banco de produção.

---

## Rotas

Rotas usadas pelas telas (tabela `routes` + [App/Route.php](App/Route.php)):

| Método | Rota | Action |
|---|---|---|
| GET | `/sign-in` | `LoginController::signin` |
| POST | `/signin` | `LoginController::actionSignIn` |
| GET | `/sign-up` | `LoginController::signUp` |
| POST | `/signup` | `LoginController::actionSignUp` |
| GET | `/forgot-password` | `LoginController::forgotPassword` |
| GET | `/signout` | `LoginController::actionSignOut` |
| GET | `/dashboard/researcher` | `dashboardResearcher` |
| GET | `/dashboard/researcher/research-studies` | `researchStudies` |
| GET | `/dashboard/researcher/research/new` | `newResearch` |
| POST | `/dashboard/researcher/research/upload-file` | `researchUpload` |
| GET | `/dashboard/researcher/research/info` | `researchInfo` |
| POST | `/dashboard/researcher/research/insert` | `researchInsert` |
| GET | `/dashboard/researcher/research/view/{id}` | `researchView` |
| POST | `/dashboard/researcher/research/insertResult` | `researchInsertResult` |
| POST | `/dashboard/researcher/research/updateporosity` | `researchUpdatePorosity` |
| GET | `/dashboard/researcher/research/filter-simulation-create/{id}` | `filterSimulationCreate` |
| POST | `/dashboard/researcher/filterSimulationSave` | `filterSimulationSave` |
| GET | `/dashboard/researcher/research/filter-simulation-view/{id}` | `FilterSimulationView` |
| GET | `/dashboard/administrator/my-profile` | `administratorMyProfile` |
| POST | `/dashboard/account/update-profile` | `actionUpdateMyProfile` |
| GET | `/dashboard/administrator/my-password` | `updateMyPassword` |
| POST | `/dashboard/administrator/update-my-password` | `actionUpdateMyPassword` |
| GET | `/dashboard/administrator/my-photo` | `uploadMyPhoto` |
| POST | `/dashboard/administrator/upload-photo` | `uploadPhoto` |
| GET | `/dashboard/administrator/update-foto/{id}` | `updateMyPhoto` |
| GET | `/dashboard/administrator` | `dashboard` (admin) |
| GET | `/dashboard/administrator/researchers` | `pageResearchers` (admin) |
| GET | `/dashboard/administrator/researchers/{id}` | `adminResearcherView` (admin) |
| GET | `/dashboard/administrator/researcher/edit/{id}` | `researcherFormEdit` (admin) |
| POST | `/dashboard/administrator/update-profile` | `updateProfile` (admin) |
| GET | `/dashboard/administrator/research-studies` | `pageResearchs` (admin) |
| GET | `/dashboard/administrator/dataset` | `adminDataset` (admin) |
| GET | `/dashboard/administrator/dataset/download` | `adminDatasetDownload` (admin) |
| POST | `/api_proxy.php?target=haralick\|porosity` | arquivo físico, fora do roteador |

> Os slugs exatos das rotas que estão no banco devem ser conferidos na tabela `routes`. A lista acima foi montada a partir dos links e formulários das views.

---

## Instalação e configuração

### Requisitos

- PHP 7.4+ (recomendado 8.x) com as extensões listadas acima;
- MySQL 5.7+ / MariaDB 10.3+;
- Apache ou LiteSpeed com `mod_rewrite` e `AllowOverride All`;
- Composer (o `composer.phar` já vem na raiz).

### Passos

```bash
# 1. Dependências
php composer.phar install

# 2. Ambiente
cp .env.example .env
# edite o .env (ver tabela abaixo)

# 3. Banco
#    crie o banco, rode o script corrigido (ver "Banco de dados"),
#    popule `country` e `routes` e crie um usuário administrador
#    (login.log_type = 'A'; gere o hash com
#     php -r "echo password_hash('SENHA', PASSWORD_DEFAULT);")

# 4. Permissões de escrita
#    resources/dashboard/assets/img/research/
#    resources/dashboard/assets/img/researcher/
#    resources/dashboard/assets/img/administrator/

# 5. Aponte o DocumentRoot para a raiz do projeto
```

> O código usa caminhos absolutos a partir da raiz (`/dashboard/...`), então a aplicação deve rodar na **raiz do domínio/subdomínio**, não numa subpasta.

### Variáveis do `.env`

| Variável | Uso |
|---|---|
| `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` | Conexão MySQL |
| `BASE_URL` | URL base com `/` final (ex.: `https://empp.exemplo.com/`) |
| `BASE_ASSETS`, `BASE_CSS`, `BASE_JS`, `BASE_VENDOR`, `BASE_IMG` | URLs dos assets (`BASE_IMG` aponta para `resources/dashboard/assets/img/`) |
| `SITE_TITLE_HOME` e demais `SITE_*` | Título e metadados do site |
| `DEV_*` | Créditos do rodapé |
| `JWT_SECRET` | Reservado (JWT ainda não usado) |
| `MAIL_HOST`, `MAIL_PORT`, `MAIL_USER`, `MAIL_PASS`, `MAIL_FROM` | SMTP (reservado para recuperação de senha) |
| `GOOGLE_MAPS_API_KEY` | Reservado |

> O `.env.example` lista só parte das variáveis. `BASE_ASSETS`, `BASE_CSS`, `BASE_JS`, `BASE_VENDOR`, `BASE_IMG` e `SITE_TITLE_HOME` são **obrigatórias** para as telas renderizarem.

---

## Scripts utilitários de deploy

Servem para hospedagem sem SSH (LiteSpeed/lsapi):

| Script | Função | Segurança |
|---|---|---|
| [diag.php](diag.php) | Mostra a versão do PHP, as extensões, os arquivos-chave com permissões, os arquivos `vendor` ilegíveis ou vazios, o teste de autoload, a presença (nunca o valor) das variáveis do `.env` e o teste de conexão MySQL. | Exige o token do arquivo `.diag_token` (32+ caracteres, comparação com `hash_equals`) e **se apaga** depois de rodar. Erros vão só para o `error_log`. |
| [fix_perms.php](fix_perms.php) | Aplica `chmod` 755 em diretórios e 644 em arquivos, recursivamente, e confere o `vendor/symfony/deprecation-contracts/function.php`. | Token **fixo no código** (`TROQUE_ESTE_TOKEN`). Troque antes de subir e **apague logo depois de usar**. |
| `info.php` | `phpinfo()` | **Não deve ficar em produção.** |

---

## Limitações e pontos de atenção

**Segurança**
- Senhas antigas ainda podem estar em **SHA-1 sem salt** no banco. Elas são convertidas para bcrypt no próximo login de cada usuário; contas que nunca mais entrarem continuam em SHA-1 até isso acontecer.
- O cadastro não verifica se o e-mail já existe e não confirma o e-mail (`log_confirmed` não é usado).
- Não há proteção CSRF nos formulários (há um campo `csrf_token` no upload, sem validação no servidor).
- O upload de imagem valida pelo MIME enviado pelo navegador. Vale validar também com `finfo`/`getimagesize` e reconverter de fato para JPG (hoje o arquivo só é renomeado para `.jpg`).
- O `.htaccess` **não** bloqueia `.env` nem `.diag_token`, ao contrário do que diz o comentário do `diag.php`. Adicione regras para negar acesso a arquivos que começam com ponto.
- `info.php` e `fix_perms.php` não devem ficar no servidor.

**Funcionalidades incompletas**
- A recuperação de senha é só a tela.
- `filter_simulation_edit.php` existe, mas a action `filterSimulationUpdate` não foi implementada.
- `getDistribution` renderiza `filter_distribution`, que não existe.
- As tabelas `filter_distribution` e `distribution` e as colunas `sim_*` de KPIs não são gravadas: os KPIs são recalculados a cada visualização.
- `Filter_SimulationDAO::update` e `SimulationDAO::update` montam a query mas não chamam `execute()`.

**Dados**
- O schema no repositório diverge do código (ver [Banco de dados](#banco-de-dados)).
- Composição, rotação, translação e tamanho de poro não são coletados, embora existam no modelo de dados e no layout do CSV.

---

## Autoria

**Paradigma Design Studio** — contato@paradigmadesign.com.br
