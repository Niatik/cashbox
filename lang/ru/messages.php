<?php

return [
    // Actions
    'edit' => 'Изменить',
    'delete' => 'Удалить',
    'delete_selected' => 'Удалить выбранные',
    'create' => 'Создать',
    'save' => 'Сохранить',
    'cancel' => 'Отменить',
    'view' => 'Просмотр',

    // Validation messages
    'payment_required' => 'Необходимо добавить хотя бы один платеж',
    'payment_min' => 'Необходимо добавить хотя бы один платеж',

    // Status messages
    'no_orders' => 'Нет заказов',
    'in_use' => 'Используется',
    'can_delete' => 'Можно удалить',

    // Notifications
    'cannot_delete_source' => 'Невозможно удалить источник',
    'source_in_use' => 'Этот источник используется в :count заказах. Сначала удалите или измените источник в связанных заказах.',
    'sources_in_use' => "Источники ':names' используются в заказах. Сначала удалите или измените источники в связанных заказах.",

    // Empty states
    'no_sources' => 'Нет источников',
    'no_sources_description' => 'Создайте первый источник, чтобы отслеживать откуда приходят клиенты.',
    'no_drafts' => 'Нет черновиков',
    'no_drafts_description' => 'Черновики бронирований будут отображаться здесь',
    'no_social_media_data' => 'Данные по социальным медиа за выбранный период не найдены',
    'no_expense_data' => 'Данные по расходам за выбранный период не найдены',

    // Modal headings
    'create_rate' => 'Создание тарифа',
    'create_salary_rate' => 'Создание ставки зарплаты',
    'create_factor' => 'Создание коэффициента',

    // Helper texts
    'source_helper' => 'Укажите название источника, откуда приходят клиенты',

    // Placeholders
    'source_placeholder' => 'Например: Instagram, WhatsApp, Телефон',
    'select_date' => 'Выберите дату',

    // CashReport sections
    'basic_info' => 'Основная информация',
    'income_section' => 'Доходы',
    'expense_section' => 'Расходы',
    'salary_section' => 'Зарплаты',
    'final_balance' => 'Итоговый баланс',

    // Payments section
    'payments_section' => 'Оплаты',

    // Generic
    'for_selected_service' => 'для выбранной услуги',

    // Page titles
    'customer_sources' => 'Источники клиентов',
    'create_source' => 'Создать источник',
    'edit_source' => 'Редактировать источник',
    'daily_report' => 'Отчет за день',

    // Page actions
    'create_source_button' => 'Создать источник',
    'create_job_title_button' => 'Создать должность',
    'delete_job_title' => 'Удалить должность',
    'create_service_button' => 'Создать услугу',
    'delete_service' => 'Удалить услугу',
    'save_draft' => 'Сохранить черновик',
    'back_to_list' => 'Вернуться к списку',
    'start' => 'Старт',
    'payout' => 'Выплата',

    // Page notifications
    'source_created' => 'Источник успешно создан',
    'source_updated' => 'Источник успешно обновлен',

    // Order wizard steps
    'wizard_service' => 'Услуга',
    'wizard_service_description' => 'Выберите услугу, время и количество людей',
    'wizard_payment' => 'Оплата',
    'wizard_payment_description' => 'Внесите оплату за услугу и завершите оформление',

    // Work session fields
    'session_expenses' => 'Расходы смены',
    'session_expense_type' => 'Тип расхода',
    'session_salary' => 'Зарплата смены',
    'balance' => 'Баланс',
    'total_income' => 'Общий доход',
    'total_expense' => 'Общий расход',
    'salary_total' => 'Итого зарплата',
    'payout_amount' => 'Сумма выплаты',
    'cash_payment' => 'Наличные',
];
