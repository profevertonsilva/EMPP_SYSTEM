<?php
/*
  6-digit code email, for sign-up ($purpose 'verify') and password reset ($purpose 'reset').
  Rendered by App\Mail\Mailer with $purpose, $name, $code, $minutes.
  Email clients ignore <style> unevenly, so every rule is inline and the layout is tables.
  Tokens follow DESIGN.md: deep field header, white sheet on the page ground, the code as a
  mono readout split into hairline cells, the four-step path as numbered markers.
*/
$e = function($v){ return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };
$sans = "'IBM Plex Sans', -apple-system, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif";
$mono = "'IBM Plex Mono', 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace";
$firstName = trim(explode(' ', trim($name))[0]);
$greeting = $firstName === '' ? 'Hi' : 'Hi ' . $e($firstName);
$isReset = ($purpose ?? 'verify') === 'reset';
$copy = $isReset ? [
    'title' => 'Your EMPP password reset code',
    'heading' => 'Reset your password',
    'intro' => 'type this code on the reset page, together with your new password.',
    'label' => 'Password reset code',
    'ignore' => "Didn't ask to reset your password? Ignore this email; your password stays the same.",
] : [
    'title' => 'Your EMPP verification code',
    'heading' => 'Confirm your email',
    'intro' => 'type this code on the verification page to activate your EMPP researcher account.',
    'label' => 'Verification code',
    'ignore' => "Didn't create an EMPP account? Ignore this email; the account stays inactive and no one can sign in with it.",
];
$steps = [
    'SEM image + electrospinning parameters',
    'Haralick texture features',
    'Predicted porosity',
    'Filtration performance simulation',
];
?>
<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="color-scheme" content="light" />
  <meta name="supported-color-schemes" content="light" />
  <title><?= $copy['title']; ?></title>
  <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@500&family=IBM+Plex+Sans:wght@400;600&display=swap" rel="stylesheet" />
  <style>
    @media (max-width: 600px) {
      .sheet-pad { padding-left: 24px !important; padding-right: 24px !important; }
      .digit { font-size: 28px !important; width: 40px !important; height: 52px !important; }
    }
  </style>
</head>
<body style="margin:0; padding:0; background-color:#f3f4f6; -webkit-text-size-adjust:100%;">

  <!-- Inbox preview line -->
  <div style="display:none; max-height:0; overflow:hidden; mso-hide:all; font-size:1px; line-height:1px; color:#f3f4f6;">
    Your EMPP code is <?= $e($code); ?>. It is valid for <?= $e($minutes); ?> minutes.&#8199;&#847;&#8199;&#847;&#8199;&#847;&#8199;&#847;&#8199;&#847;&#8199;&#847;
  </div>

  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f3f4f6;">
    <tr>
      <td align="center" style="padding:40px 12px;">

        <table role="presentation" width="560" cellpadding="0" cellspacing="0" border="0" style="width:100%; max-width:560px;">

          <!-- Deep field: the brand's entry frame -->
          <tr>
            <td style="background-color:#1a2547; border-radius:6px 6px 0 0; padding:28px 40px 26px;" class="sheet-pad">
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                <tr>
                  <td valign="middle">
                    <img src="cid:empp-logo" width="112" alt="EMPP" style="display:block; width:112px; height:auto; border:0; outline:none;" />
                  </td>
                  <td valign="middle" align="right" style="font-family:<?= $sans; ?>; font-size:13px; line-height:18px; color:#aab4d6;">
                    Electrospun Membrane<br />Property Predictor
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- White sheet -->
          <tr>
            <td style="background-color:#ffffff; border:1px solid #e1e4ea; border-top:0; padding:40px 40px 36px;" class="sheet-pad">

              <h1 style="margin:0 0 12px; font-family:<?= $sans; ?>; font-size:24px; line-height:30px; font-weight:600; letter-spacing:-0.4px; color:#19202e;">
                <?= $copy['heading']; ?>
              </h1>
              <p style="margin:0 0 32px; font-family:<?= $sans; ?>; font-size:15px; line-height:23px; color:#3c4557;">
                <?= $greeting; ?>, <?= $copy['intro']; ?>
              </p>

              <!-- The code as an instrument readout: one bordered strip, hairline-divided digits -->
              <p style="margin:0 0 10px; font-family:<?= $mono; ?>; font-size:11px; line-height:14px; font-weight:500; letter-spacing:1px; text-transform:uppercase; color:#6a7284;">
                <?= $copy['label']; ?>
              </p>
              <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #cdd2db; border-radius:4px; border-collapse:separate;">
                <tr>
                  <?php foreach (str_split($code) as $i => $digit): ?>
                  <td class="digit" align="center" valign="middle" width="56" height="64"
                      style="width:56px; height:64px; <?= $i > 0 ? 'border-left:1px solid #e1e4ea;' : ''; ?> font-family:<?= $mono; ?>; font-size:36px; line-height:64px; font-weight:500; color:#19202e; font-variant-numeric:tabular-nums;">
                    <?= $e($digit); ?>
                  </td>
                  <?php endforeach; ?>
                </tr>
              </table>
              <p style="margin:12px 0 0; font-family:<?= $sans; ?>; font-size:13px; line-height:19px; color:#6a7284;">
                Valid for <span style="font-family:<?= $mono; ?>; color:#3c4557;"><?= $e($minutes); ?>&nbsp;min</span>. A new code replaces this one.
              </p>

              <?php if (!$isReset): ?>
              <!-- What the account opens: the four-step path -->
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top:36px; border-top:1px solid #e1e4ea;">
                <tr>
                  <td style="padding:24px 0 14px; font-family:<?= $sans; ?>; font-size:15px; line-height:20px; font-weight:600; color:#19202e;">
                    Once confirmed, each study runs
                  </td>
                </tr>
                <?php foreach ($steps as $i => $step): ?>
                <tr>
                  <td style="padding:5px 0;">
                    <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                      <tr>
                        <td align="center" valign="middle" width="24" height="24"
                            style="width:24px; height:24px; border:2px solid <?= $i === 0 ? '#34498c' : '#cdd2db'; ?>; border-radius:50%; font-family:<?= $mono; ?>; font-size:11px; line-height:24px; font-weight:500; color:<?= $i === 0 ? '#34498c' : '#6a7284'; ?>;">
                          <?= $i + 1; ?>
                        </td>
                        <td style="padding-left:12px; font-family:<?= $sans; ?>; font-size:14px; line-height:20px; color:#3c4557;">
                          <?= $e($step); ?>
                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>
                <?php endforeach; ?>
              </table>
              <?php endif; ?>

            </td>
          </tr>

          <!-- Record footer on the page ground -->
          <tr>
            <td style="background-color:#f3f4f6; border:1px solid #e1e4ea; border-top:0; border-radius:0 0 6px 6px; padding:20px 40px;" class="sheet-pad">
              <p style="margin:0; font-family:<?= $sans; ?>; font-size:13px; line-height:19px; color:#5b6376;">
                <?= $copy['ignore']; ?>
              </p>
            </td>
          </tr>

          <tr>
            <td align="center" style="padding:24px 12px 0; font-family:<?= $sans; ?>; font-size:12px; line-height:18px; color:#5b6376;">
              EMPP &middot; Doctoral research by Everton Rafael da Silva<br />
              Sent from an unmonitored address; replies are not read.
            </td>
          </tr>

        </table>

      </td>
    </tr>
  </table>
</body>
</html>
