<?php

use App\Kernel\AppContext;
use App\Kernel\Config;
use App\Kernel\Factory;
use App\Kernel\Front\Translate;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Routing\RouteCollectorProxy;

$app->group('/secured', function (RouteCollectorProxy $app) {

    // CONNEXION
    $app->map(['GET', 'POST'], '/login', function (Request $req, Response $res, array $args): Response {
        return \App\Kernel\AppContext::twig()->render($res, 'secured/login.twig.html');
    })->setName('secured_login');

    // UPLOAD AVATAR
    $app->post('/upload', function (Request $req, Response $res, array $args): Response {
        $body      = $req->getParsedBody();
        $fieldName = is_array($body) ? ($body['field'] ?? '') : '';

        $name      = basename($_FILES[$fieldName]['name'] ?? '');
        $ext       = explode('.', $name);
        $extension = end($ext);
        $name      = basename($name, '.' . $extension);
        $name      = Factory::getInstance()->Url()->encode($name) . '_' . time() . '.' . $extension;

        $rst = move_uploaded_file($_FILES[$fieldName]['tmp_name'] ?? '', IMAGE_PATH . '/_avatar/' . $name);

        $std       = new \stdClass;
        $std->name = str_replace(WEB_PATH, '', IMAGE_PATH . '/_avatar/' . $name);
        $std->url  = Factory::getInstance()->Url()->image('_avatar/' . $name, true);
        $std->rst  = $rst;

        Factory::getInstance()->Response()->printJSON($std);
        return $res;
    });

    // PROFIL
    $app->map(['GET', 'POST'], '/profile', function (Request $req, Response $res, array $args): Response {
        $config   = Config::getInstance();
        $error    = false;
        $tabError = [];
        $id       = $_SESSION[$config->get('session', '')]['id'] ?? null;

        $user = \DB::for_table('user')
            ->where_equal('user_id', $id)
            ->find_one();

        if (!$user) {
            Factory::getInstance()->Response()->redirectHome();
        }

        $body = $req->getParsedBody();
        $post = fn(string $k) => (is_array($body) ? ($body[$k] ?? '') : '');

        if (strtoupper($req->getMethod()) === 'POST') {
            if ($post('user_name') == '') {
                $error = true;
                $tabError['user_name'] = Translate::getInstance()->getText('mandatory_fillin');
            } else {
                $exist = \DB::for_table('user')
                    ->where_equal('user_name', $post('user_name'))
                    ->where_not_equal('user_id', $id)
                    ->count();
            }

            if ($post('user_name') != '' && ($exist ?? 0) > 0) {
                $error = true;
                $tabError['user_name'] = Translate::getInstance()->getText('already_use_login');
            }

            foreach (['user_fname', 'user_lname'] as $field) {
                if ($post($field) == '') {
                    $error = true;
                    $tabError[$field] = Translate::getInstance()->getText('mandatory_fillin');
                }
            }

            if ($post('last_password') != '' && password_verify($post('last_password'), $user->user_password) === false) {
                $error = true;
                $tabError['last_password'] = Translate::getInstance()->getText('err_password_old');
            }

            if ($post('last_password') == '' && $post('password') != '' && $post('confirm_password') != '') {
                $error = true;
                $tabError['last_password'] = Translate::getInstance()->getText('mandatory_fillin');
            }

            if ($post('last_password') != '' && $post('password') != $post('confirm_password')) {
                $error = true;
                $tabError['confirm_password'] = Translate::getInstance()->getText('msg_different_password');
            }

            if ($error === false) {
                if ($post('password') != '') {
                    $user->user_password = password_hash($post('password'), PASSWORD_BCRYPT, ['cost' => 9]);
                }
                $user->user_name  = $post('user_name');
                $user->user_fname = $post('user_fname');
                $user->user_lname = $post('user_lname');
                $user->save();

                Factory::getInstance()->Response()->flashAndRedirect('Votre profil est modifié', true, '/secured/profile');
            }
        }

        return \App\Kernel\AppContext::twig()->render($res, 'secured/profile.twig.html', [
            'error'    => ($error === false ? '0' : '1'),
            'tabError' => json_encode($tabError),
        ]);
    })->setName('secured_profile');

    // DÉCONNEXION
    $app->get('/logout', function (Request $req, Response $res, array $args): Response {
        return $res;
    })->setName('secured_logout');

    // ACCÈS INTERDIT
    $app->get('/forbidden', function (Request $req, Response $res, array $args): Response {
        return \App\Kernel\AppContext::twig()->render($res, 'errors/403.twig');
    })->setName('secured_forbidden');
});
