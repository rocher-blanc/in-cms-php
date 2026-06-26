# Changelog — feat/php82-compat

## Slim 4 / Twig 3 / PHP 8.2

### Breaking changes

- **Slim 2 → 4** : bootstrap via `AppFactory`, routes PSR-7, middlewares PSR-15
- **`$app->config()`** → `Config::getInstance()->get()`
- **`$app->render()`** → `AppContext::twig()->render($res, ...)`
- **`$app->redirect()`** → `Factory::getInstance()->Response()->redirect()` (lève `RedirectException`)
- **`$app->flash()`** → `AppContext::flash()->addMessage()`
- **`->name()`** → `->setName()` sur les routes Slim 4
- **`->via()` / `->conditions()` / `$app->pass()`** : supprimés (Slim 4)
- **Twig 1 → 3** : `addGlobal()` figé après le 1er rendu ; globals enregistrés avant rendu
- **PHPMailer 5 → 6** : namespaces `PHPMailer\PHPMailer`
- **facebook/graph-sdk** → **joelbutcher/facebook-graph-sdk** (fork PHP 8)
- **Factory `String`** → **`Str`** (`String` est un mot réservé PHP 8)

### Correctifs intégration (cl-sefepar)

- `NotFoundException` : 404 propre sans double `HttpNotFoundException`
- Plugins `Language` / `Meta` chargés **avant** `Router` (globals Twig)
- `Meta.php` : accès sécurisé `seo_gtm`, `seo_matomo`, etc. (BDD vide)
- `viewTemplateError()` : résolution `.twig` / `.twig.html` + `TEMPLATES_COMMON_TECH_PATH` en back
- `functions.php` : guard `function_exists('dd')`
- `Http.php` : try/catch chargement CDN
- `AppContext::addGlobal()` : try/catch Twig 3

### Composer (projet client)

```json
"repositories": [{ "type": "vcs", "url": "https://github.com/rocher-blanc/in-cms-php.git" }],
"require": { "jwebcreation/cms": "dev-feat/php82-compat" }
```
