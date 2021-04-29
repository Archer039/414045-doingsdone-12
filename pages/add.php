<?php
/**
 * @var int $user_id
 */

require_once('../bootstrap.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // проверяем есть ли ошибки валидачии
    $errors = validate_task_form(
        user_id: $user_id,
        task_name: $_POST['name'],
        project_id: $_POST['project_id'],
        task_date: $_POST['date']
    );

    // сохраняем задачу в БД и файл в корень проекта
    if ($errors) {
        $current_page = 'add_task';
        $layout_data = get_layout_data(
            user_id: $user_id,
            current_page: $current_page,
            errors: $errors
        );
        print(include_template('layout.php', $layout_data));
        exit(1);
    }
    add_new_task(
        user_id: $user_id,
        project_id: $_POST['project_id'],
        task_name: $_POST['name'],
        task_date: $_POST['date'],
        file: $_FILES['file']
    );

    // перенаправляем на главную страницу
    header('Location: /');
}







