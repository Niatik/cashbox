import './bootstrap';
// Импортируем нашу функцию
import setupEnterNavigation from './filament-enter-navigation.js';

// Инициализируем функцию как можно раньше
document.addEventListener('DOMContentLoaded', () => {
    // Запускаем настройку и сохраняем ссылку на функцию обновления
    const refreshEnterNavigation = setupEnterNavigation();

    // Добавляем обработчик для перехвата отправки формы на уровне документа
    document.addEventListener('submit', function(e) {
        const activeElement = document.activeElement;

        // Если активный элемент - поле ввода (не кнопка отправки),
        // и нет явного элемента, вызвавшего отправку
        if (activeElement &&
            activeElement.tagName !== 'BUTTON' &&
            activeElement.type !== 'submit' &&
            e.submitter === null) {

            // Предотвращаем отправку формы
            e.preventDefault();

            // Дополнительно перезапускаем наши обработчики
            refreshEnterNavigation();

            return false;
        }
    }, true); // Используем capture phase

    // Для альтернативных случаев вызова отправки формы
    document.addEventListener('click', function(e) {
        // Если клик произошел на элементе с data-атрибутом для отправки формы
        if (e.target.matches('[data-submit-form]')) {
            // Проверяем состояние клавиши Enter
            if (window.event && window.event.keyCode === 13) {
                e.preventDefault();
                return false;
            }
        }
    }, true);
});
