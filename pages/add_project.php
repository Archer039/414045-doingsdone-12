<?php
/**
 * @var array $layout_data данные для шаблона layout.php
 * @var int   $user_id     id пользователя
 */

require_once('../bootstrap.php');

redirect_guest();

$layout_data['content'] = include_template(
    'form_project.php',
    get_form_project_data(
        $user_id
    )
);

print (include_template('layout.php', $layout_data));