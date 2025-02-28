export default function setupEnterNavigation() {
    // Функция перехвата отправки формы
    function preventFormSubmit(e) {
        const target = e.target;

        // Проверяем, что нажата клавиша Enter в поле формы
        if (e.key === 'Enter' &&
            (target.tagName === 'INPUT' || target.tagName === 'SELECT') &&
            target.type !== 'textarea' &&
            target.type !== 'submit' &&
            target.type !== 'button') {

            // Предотвращаем действие по умолчанию
            e.preventDefault();

            // Ищем все элементы формы, которые могут получить фокус
            const form = target.closest('form') || target.closest('.fi-form');
            if (!form) return;

            const formElements = Array.from(form.querySelectorAll(
                'input:not([type="hidden"]):not([type="submit"]):not([disabled]), ' +
                'select:not([disabled]), ' +
                'textarea:not([disabled]), ' +
                'button:not([disabled]), ' +
                '[contenteditable="true"], ' +
                '.choices__inner, ' +
                '[tabindex]:not([tabindex="-1"])'
            ));

            // Получаем текущий индекс в массиве focusable элементов
            const currentIndex = formElements.indexOf(target);

            // Находим следующий элемент для фокуса
            if (currentIndex > -1 && currentIndex < formElements.length - 1) {
                const nextElement = formElements[currentIndex + 1];

                // Задержка для предотвращения срабатывания на текущем элементе
                setTimeout(() => {
                    nextElement.focus();

                    // Если это селект, имитируем клик для открытия
                    if (nextElement.tagName === 'SELECT') {
                        const event = new MouseEvent('mousedown');
                        nextElement.dispatchEvent(event);
                    }

                    // Для Choices.js
                    if (nextElement.classList.contains('choices__inner')) {
                        nextElement.click();
                    }
                }, 10);
            }
        }
    }

    // Перехват submit-события на уровне формы
    function handleFormSubmit(e) {
        // Проверяем, было ли событие вызвано нажатием клавиши Enter
        // в поле ввода (не в кнопке отправки)
        const activeElement = document.activeElement;
        if (activeElement &&
            activeElement.tagName !== 'BUTTON' &&
            activeElement.type !== 'submit' &&
            e.submitter === null) {

            // Если отправка формы произошла из-за Enter в поле ввода,
            // предотвращаем отправку
            e.preventDefault();
        }
    }

    // Функция для добавления обработчиков на все формы
    function setupFormsListeners() {
        // 1. Глобальный обработчик для всех нажатий Enter в полях ввода
        document.removeEventListener('keydown', preventFormSubmit);
        document.addEventListener('keydown', preventFormSubmit, true);

        // 2. Обработчик для предотвращения отправки форм по Enter
        const forms = document.querySelectorAll('form, .fi-form');
        forms.forEach(form => {
            form.removeEventListener('submit', handleFormSubmit);
            form.addEventListener('submit', handleFormSubmit, true);
        });
    }

    // Инициализация при загрузке страницы
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', setupFormsListeners);
    } else {
        setupFormsListeners();
    }

    // Обработка динамически добавляемых форм и полей
    document.addEventListener('livewire:navigated', setupFormsListeners);
    document.addEventListener('livewire:load', setupFormsListeners);

    // Реинициализация при изменении DOM
    const observer = new MutationObserver(setupFormsListeners);
    observer.observe(document.body, {
        childList: true,
        subtree: true
    });

    // Возвращаем функцию для ручного обновления
    return setupFormsListeners;
}
