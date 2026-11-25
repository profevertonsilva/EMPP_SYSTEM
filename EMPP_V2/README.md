# EMPP v2

Reconstrução do EMPP (Electrospun Membrane Property Predictor) a partir da especificação v2.1
(pacote "EMPP - interface", Prof. Murilo Innocentini). Vive nesta pasta do repositório, isolada
do sistema em produção (raiz), que não depende dela. O `.htaccess` da raiz bloqueia `/EMPP_V2/`
por HTTP: nada daqui é servido enquanto a v2 não tiver ponto de entrada próprio.

## Estado

Fase 1 da ordem sugerida: **modelo de filtração v2.1 e alertas, em PHP, com testes.** Ainda sem
telas, rotas ou banco.

```
src/Model/
  FiltrationModel.php   simulação (porte de emppSimulate): M1–M6, opções legacy e inertial
  SimulationInput.php   entrada validada (unidades do modelo de referência)
  SimulationOptions.php legacy | inertial
  SimulationResult.php  resultado; penetração é o valor primário (eficiência = 1 − pen)
  Air.php               propriedades do ar (T, P)
  Alerts.php            13 alertas, níveis NOTE e CHECK (porte de emppAlerts)
  En1822.php            classes E10–U17 e projeção para a próxima classe
  Respirator.php        FFP1–3, N95/N99/N100 (critério de eficiência)
  Iso16890.php          ePM1, ePM2,5, ePM10 e grupo
  Presets.php           cenários de norma (EN 1822, NIOSH N, NIOSH R/P) e ar (Ribeirão Preto, nível do mar)
reference/03_modelo_referencia.js   modelo de referência em JS, sem alteração
tests/fixtures/                     saída do modelo de referência para 13 cenários
```

## Testes

```
composer install
composer test
```

- `FiltrationModelTest`: os casos publicados na seção 6 da especificação (planilha, EN 1822,
  NIOSH N, NIOSH P, NIOSH N com termo inercial, ePM), conferidos com as casas decimais publicadas.
- `ReferenceParityTest`: todos os valores escalares, as 100 linhas da curva fracionária, os
  alertas (texto incluso), a classe e os ePM contra o JS de referência, com tolerância relativa 1e-9.
- `ClassificationTest`: limites das classes, exemplo da Membrana A do manual (H13: 16,0 µm,
  552 Pa, +21 min), respiradores, validação de entrada.

A fixture só deve ser regenerada se o modelo de referência mudar:

```
node tests/fixtures/generate-reference.js
```

## Pendências da especificação

- M3: conferir a forma da correção de escorregamento na referência original (Pich 1966 / Brown 1993).
- O alerta R1 (MPPS maior que a fibra) não ocorre com entradas realistas neste modelo (MPPS/df
  fica abaixo de 0,91 numa varredura ampla); é testado com resultado sintético.
