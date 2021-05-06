<?php
/**
 * @var array-key $projects
 * @var int       $chosen_project_id
 * @var string    $chosen_tasks_filter
 * @var array-key $user_tasks
 * @var array-key $found_tasks
 * @var array     $projects_count
 * @var int       $show_complete_tasks
 */

?>

<section class="content__side">
    <h2 class="content__side-heading">Проекты</h2>

    <nav class="main-navigation">
        <ul class="main-navigation__list">
            <!-- добавляем цикл с массивом проектов -->
            <?php
            foreach ($projects as $key => $project): ?>
                <li class="main-navigation__list-item<?= ($project['id']
                    == $chosen_project_id)
                    ? " main-navigation__list-item--active" : "" ?>">
                    <a class="main-navigation__list-item-link"
                       href="/?id_chosen_project=<?= $project['id'] ?>"><?= filter(
                            $project['name']
                        ); ?></a>
                    <span class="main-navigation__list-item-count"><?= $projects_count[$project['id']]
                        ?? 0 ?></span>
                </li>
            <?php
            endforeach; ?>
        </ul>
    </nav>

    <a class="button button--transparent button--plus content__side-button"
       href="/?page=add_project" target="project_add">Добавить
        проект</a>
</section>

<main class="content__main">
    <h2 class="content__main-heading">Список задач</h2>

    <form class="search-form" action="/index.php" method="get"
          autocomplete="off">
        <input class="search-form__input" type="text" name="search" value=""
               placeholder="Поиск по задачам">
        <input class="search-form__submit" type="submit" name="" value="Искать">
    </form>

    <div class="tasks-controls">
        <nav class="tasks-switch">
            <a href="/?tasks_filter=all<?= $chosen_project_id
                ? '&id_chosen_project='.$chosen_project_id : '' ?>"
               class="tasks-switch__item<?= $chosen_tasks_filter == 'all'
                   ? ' tasks-switch__item--active' : '' ?>">Все
                задачи</a>
            <a href="/?tasks_filter=today_tasks"
               class="tasks-switch__item<?= $chosen_tasks_filter == 'today_tasks'
                   ? ' tasks-switch__item--active' : '' ?>">Повестка дня</a>
            <a href="/?tasks_filter=next_day_tasks" class="tasks-switch__item<?= $chosen_tasks_filter == 'next_day_tasks'
                ? ' tasks-switch__item--active' : '' ?>">Завтра</a>
            <a href="/?tasks_filter=overdue_tasks" class="tasks-switch__item<?= $chosen_tasks_filter == 'overdue_tasks'
                ? ' tasks-switch__item--active' : '' ?>">Просроченные</a>
        </nav>

        <label class="checkbox">
            <!--добавить сюда атрибут "checked", если переменная $show_complete_tasks равна единице-->
            <input class="checkbox__input visually-hidden show_completed"
                   type="checkbox" <?= $show_complete_tasks ? "checked" : "" ?>>
            <span class="checkbox__text">Показывать выполненные</span>
        </label>
    </div>

    <table class="tasks">
        <?= $nothing_found_message ?? '' ?>
        <!-- добавляем цикл для двумерного массива -->
        <?php
        $tasks = $user_tasks ?? $found_tasks ?? []; ?>
        <?php
        foreach ($tasks as $task): ?>
            <?php
            if ($task['is_done'] && !$show_complete_tasks): ?>
                <?php
                continue ?>
            <?php
            endif; ?>
            <tr class="tasks__item task <?= $task['is_done'] ? "task--completed"
                : "" ?><?= is_task_important($task['time_end'])
                ? " task--important" : "" ?>">
                <td class="task__select">
                    <label class="checkbox task__checkbox">
                        <input class="checkbox__input visually-hidden task__checkbox"
                               type="checkbox" value="<?= $task['id'] ?>" <?= $task['is_done'] ? 'checked' : '' ?>>
                        <span class="checkbox__text"><?= filter(
                                $task['name']
                            ); ?></span>
                    </label>
                </td>
                <td class="task__file">
                    <a class="download-link"
                       href="<?= $task['file_src']; ?>"><?= $task['file_name']; ?></a>
                </td>
                <td class="task__date"><?= filter($task['time_end']); ?></td>
            </tr>
        <?php
        endforeach; ?>
    </table>
</main>
