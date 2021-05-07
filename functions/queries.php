<?php

/**
 * Поиск id выбранного проекта пользователя
 *
 * @param int $user_id    id пользователя
 * @param int $project_id id проекта
 *
 * @return array|false найденные id, false в случае ошибки
 */
function find_project_id($user_id, $project_id)
{
    $con = connect_db();
    $sql = "SELECT id FROM projects WHERE user_id = ? AND id = ?";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, 'ii', $user_id, $project_id);
    mysqli_stmt_execute($stmt);
    $result_sql = mysqli_stmt_get_result($stmt);

    if (mysqli_stmt_error($stmt)) {
        return false;
    }

    $project_id_sql = mysqli_fetch_assoc($result_sql);

    return $project_id_sql;
}

/**
 * Проверка существет ли проект с таким-же именем
 *
 * @param int    $user_id      id пользователя
 * @param string $project_name имя проекта
 *
 * @return bool true в случае успеха, false в случае ошибки
 */
function project_name_is_be($user_id, $project_name)
{
    $con = connect_db();
    $sql
        = "SELECT COUNT(*) AS 'count' FROM projects WHERE user_id = ? AND title = ?";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, 'is', $user_id, $project_name);
    mysqli_stmt_execute($stmt);
    $result_sql = mysqli_stmt_get_result($stmt);

    if (mysqli_stmt_error($stmt)) {
        return false;
    }

    $result = mysqli_fetch_assoc($result_sql);

    return $result['count'] > 0;
}

/**
 * Получает количество задач в проектах
 *
 * @param int $user_id id пользователя
 *
 * @return array|false количество задач в проектах, false в случае ошибки
 */
function get_count_task_in_projects($user_id)
{
    $con = connect_db();
    $sql
        = "SELECT project_id, COUNT(*) AS 'count_tasks' FROM tasks WHERE user_id = ? GROUP BY project_id";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $user_id);
    mysqli_stmt_execute($stmt);
    $result_sql = mysqli_stmt_get_result($stmt);

    if (mysqli_stmt_error($stmt)) {
        return false;
    }

    $count_tasks = [];
    $count_tasks_sql = mysqli_fetch_all($result_sql, MYSQLI_ASSOC);
    foreach ($count_tasks_sql as $value) {
        $count_tasks[$value['project_id']] = $value['count_tasks'];
    }

    return $count_tasks;
}

/**
 * Получает из БД список проектов текущего пользователя
 *
 * @param int $user_id id пользователя
 *
 * @return array|false проекты пользователя, false в случае ошибки
 */
function get_projects($user_id)
{
    $con = connect_db();
    $sql = "SELECT id, title AS name FROM projects WHERE user_id = ?";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $user_id);
    mysqli_stmt_execute($stmt);
    $result_sql = mysqli_stmt_get_result($stmt);

    if (mysqli_stmt_error($stmt)) {
        return false;
    }

    $projects_sql = mysqli_fetch_all($result_sql, MYSQLI_ASSOC);

    return $projects_sql;
}

/**
 * Получает все задачи пользователя
 *
 * @param int $user_id id выбранного пользователя
 *
 * @return array|false массив со всеми задачами пользователя, false в случае ошибки
 */
function get_user_all_tasks($user_id)
{
    $con = connect_db();
    $sql
        = "SELECT t.id, t.title AS name, time_end, p.id AS project_id, p.title AS project, is_done, file_src FROM tasks t JOIN projects p ON t.project_id = p.id WHERE t.user_id = ?";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $user_id);
    mysqli_stmt_execute($stmt);
    $result_sql = mysqli_stmt_get_result($stmt);

    if (mysqli_stmt_error($stmt)) {
        return false;
    }

    return mysqli_fetch_all($result_sql, MYSQLI_ASSOC);
}

/**
 * Получает задачи пользователя по выбранному проекту
 *
 * @param int $user_id    id выбранного пользователя
 * @param int $project_id id выбранного проекта
 *
 * @return array|false задачи пользователя по выбранному проекту, false в случае ошибки
 */
function get_user_tasks_chosen_project($user_id, $project_id)
{
    $con = connect_db();
    $sql
        = "SELECT t.id, t.title AS name, time_end, p.id AS project_id, p.title AS project, is_done, file_src  FROM tasks t JOIN projects p ON t.project_id = p.id WHERE t.user_id = ? AND t.project_id = ?";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, 'ii', $user_id, $project_id);
    mysqli_stmt_execute($stmt);
    $result_sql = mysqli_stmt_get_result($stmt);

    if (mysqli_stmt_error($stmt)) {
        return false;
    }

    return mysqli_fetch_all($result_sql, MYSQLI_ASSOC);
}

/**
 * Получает задачи пользователя с применением фильтра
 *
 * @param int    $user_id      id пользователя
 * @param string $tasks_filter фильтр задач
 *
 * @return array|false задачи пользователя с фильтром, false в случае ошибки
 */
function get_user_tasks_chosen_filter($user_id, $tasks_filter)
{
    $today = date('Y-m-d');
    $next_day = date('Y-m-d', strtotime("+1 day"));
    $con = connect_db();

    if ($tasks_filter == 'today_tasks' || $tasks_filter == 'next_day_tasks') {
        $sql
            = "SELECT t.id, t.title AS name, time_end, p.id AS project_id, p.title AS project, is_done, file_src  FROM tasks t JOIN projects p ON t.project_id = p.id WHERE t.user_id = ? AND t.time_end = ?";
        if ($tasks_filter == 'today_tasks') {
            $filter_date = $today;
        }
        if ($tasks_filter == 'next_day_tasks') {
            $filter_date = $next_day;
        }
    }
    if ($tasks_filter == 'overdue_tasks') {
        $sql
            = "SELECT t.id, t.title AS name, time_end, p.id AS project_id, p.title AS project, is_done, file_src  FROM tasks t JOIN projects p ON t.project_id = p.id WHERE t.user_id = ? AND t.time_end < ?";
        $filter_date = $today;
    }

    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, 'is', $user_id, $filter_date);
    mysqli_stmt_execute($stmt);
    $result_sql = mysqli_stmt_get_result($stmt);

    if (mysqli_stmt_error($stmt)) {
        return false;
    }

    return mysqli_fetch_all($result_sql, MYSQLI_ASSOC);
}

/**
 * Добавляет новый проект в БД
 *
 * @param int    $user_id      id пользователя
 * @param string $project_name название проекта
 *
 * @return bool true при удачном добавлении проекта в БД, false в случае ошибки
 */
function add_new_project($user_id, $project_name)
{
    $con = connect_db();
    $sql = "INSERT INTO projects (user_id, title) VALUES (?, ?)";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, 'ii', $user_id, $project_name);
    mysqli_stmt_execute($stmt);

    if (mysqli_stmt_error($stmt)) {
        return false;
    }

    return true;
}

/**
 * Добавляет новую задачу в БД
 *
 * @param int         $user_id    id пользователя
 * @param int         $project_id id проекта
 * @param string      $title      название задачи
 * @param string|null $file_src   путь к файлу
 * @param string|null $time_end   дедлайн задачи
 *
 * @return bool true в случае успеха, false в случае ошибки
 */
function add_task_in_db($user_id, $project_id, $title, $file_src, $time_end)
{
    if ($time_end == '') {
        $time_end = null;
    }
    $con = connect_db();
    $sql
        = "INSERT INTO tasks (user_id, project_id, title, file_src, time_end) VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param(
        $stmt,
        'iisss',
        $user_id,
        $project_id,
        $title,
        $file_src,
        $time_end
    );
    mysqli_stmt_execute($stmt);

    if (mysqli_stmt_error($stmt)) {
        return false;
    }

    return true;
}

/**
 * Проверяет есть ли email в БД
 *
 * @param string $email email пользователя
 *
 * @return bool true если email найден, false в случае ошибки
 */
function email_exist($email)
{
    $con = connect_db();
    $sql = "SELECT COUNT(*) AS exist FROM users WHERE email = ?";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    $result_sql = mysqli_stmt_get_result($stmt);
    $result = mysqli_fetch_assoc($result_sql);

    return $result['exist'] > 0;
}

/**
 * Сохраняет нового пользователя в БД
 *
 * @param string $email    email пользователя
 * @param string $password хеш пароля
 * @param string $name     имя пользователя
 *
 * @return bool true в случае успеха, false в случае ошибки
 */
function add_new_user($email, $password, $name)
{
    $password = password_hash($password, PASSWORD_DEFAULT);
    $con = connect_db();
    $sql = "INSERT INTO users (email, password, name) VALUES(?, ?, ?)";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, 'sss', $email, $password, $name);
    mysqli_stmt_execute($stmt);

    if (mysqli_stmt_error($stmt)) {
        return false;
    }

    return true;
}

/**
 * Получает данные для аутентификации
 *
 * Возвращает данные для аутентификации: email и хеш пароля
 *
 * @param string $email email пользователя
 *
 * @return array|false данные аунтентификации, false в случае ошибки
 */
function get_user_auth_data($email)
{
    $con = connect_db();
    $sql = "SELECT email, password FROM users WHERE email = ?";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    $result_sql = mysqli_stmt_get_result($stmt);
    $result = mysqli_fetch_assoc($result_sql);

    if (mysqli_stmt_error($stmt)) {
        return false;
    }

    return $result;
}

/**
 * Получает id пользователя
 *
 * @param string $email email пользователя
 *
 * @return int|false id пользователя, false в случае ошибки
 */
function get_user_id($email)
{
    $con = connect_db();
    $sql = "SELECT id FROM users WHERE email = ?";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    $result_sql = mysqli_stmt_get_result($stmt);
    $result = mysqli_fetch_assoc($result_sql);

    if (mysqli_stmt_error($stmt)) {
        return false;
    }

    return $result['id'];
}

/**
 * Поиск задачи
 *
 * @param int    $user_id   id пользователя
 * @param string $task_name имя задачи, поисковый запрос
 *
 * @return array|false найденые задачи, false в случае ошибки
 */
function get_looking_for_task($user_id, $task_name)
{
    $con = connect_db();
    $sql
        = "SELECT t.id, t.title AS name, time_end, p.id AS project_id, p.title AS project, is_done, file_src FROM tasks t JOIN projects p ON t.project_id = p.id WHERE t.user_id = ? AND MATCH(t.title) AGAINST(?)";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, 'is', $user_id, $task_name);
    mysqli_stmt_execute($stmt);
    $result_sql = mysqli_stmt_get_result($stmt);
    $result = mysqli_fetch_all($result_sql, MYSQLI_ASSOC);

    if (mysqli_stmt_error($stmt)) {
        return false;
    }

    $tasks = [];

    foreach ($result as $task) {
        $file_name = 'файл не загружен';
        if ($task['file_src']) {
            $file_name = ltrim($task['file_src'], '/');
        }
        $task['file_name'] = $file_name;

        if ($task['time_end']) {
            $task['time_end'] = date("d.m.Y", strtotime($task['time_end']));
        }
        array_push($tasks, $task);
    }

    return $tasks;
}

/**
 * Получение задачи
 *
 * @param int $user_id id пользователя
 * @param int $task_id id задачи
 *
 * @return array|false задача, false в случае ошибки
 */
function get_task($user_id, $task_id)
{
    $con = connect_db();
    $sql = "SELECT id, is_done FROM tasks WHERE user_id = ? AND id = ?";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, 'ii', $user_id, $task_id);
    mysqli_stmt_execute($stmt);
    $result_sql = mysqli_stmt_get_result($stmt);

    if (mysqli_stmt_error($stmt)) {
        return false;
    }

    return mysqli_fetch_assoc($result_sql);
}

/**
 * Изменение статуса задачи
 *
 * Изменяет статус выполнения задачи на противоположный
 *
 * @param int $user_id id пользователя
 * @param int $task_id id задачи
 *
 * @return bool true в случае успеха, false в случае ошибки
 */
function change_task_done_state($user_id, $task_id)
{
    $task = get_task($user_id, $task_id);
    $task['is_done'] = !$task['is_done'];
    $con = connect_db();
    $sql = "UPDATE tasks SET is_done = ? WHERE id = ?";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, 'ii', $task['is_done'], $task_id);
    mysqli_stmt_execute($stmt);

    if (mysqli_stmt_error($stmt)) {
        return false;
    }

    return true;
}
