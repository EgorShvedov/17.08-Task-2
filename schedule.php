<?php
// Генератор расписания "сутки через двое"
// Использование: php schedule.php [год] [месяц] [количество_месяцев]

function generateSchedule($year, $month, $monthsCount = 1) {
    for ($m = 0; $m < $monthsCount; $m++) {
        $currentMonth = $month + $m;
        $currentYear = $year;

        // Корректируем переполнение месяцев
        if ($currentMonth > 12) {
            $currentYear += intdiv($currentMonth - 1, 12);
            $currentMonth = (($currentMonth - 1) % 12) + 1;
        }

        // Название месяца
        $monthName = strftime('%B %Y', mktime(0, 0, 0, $currentMonth, 1, $currentYear));
        echo "\n===== $monthName =====\n";

        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $currentMonth, $currentYear);

        // Переменная для подсчёта выходных
        $nonWorkingDays = 2; // начинаем так, чтобы первый день был рабочим

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = mktime(0, 0, 0, $currentMonth, $day, $currentYear);
            $weekday = date('N', $date); // 1=Пн ... 7=Вс
            $formatted = date('d.m.Y (D)', $date);

            // По умолчанию рабочий
            $isWorkDay = false;

            // Если суббота или воскресенье → выходной
            if ($weekday == 6 || $weekday == 7) {
                $nonWorkingDays++;
                $isWorkDay = false;
            } 
            // Если ещё не накопилось 2 выходных → нерабочий
            elseif ($nonWorkingDays < 2) {
                $nonWorkingDays++;
                $isWorkDay = false;
            } 
            // Значит пришёл черёд рабочего дня
            else {
                $isWorkDay = true;
                $nonWorkingDays = 0; // обнуляем счётчик выходных
            }

            // Вывод
            if ($isWorkDay) {
                echo "\033[32m+ $formatted\033[0m\n"; // зелёный (рабочий день)
            } else {
                echo "  $formatted\n"; // обычный (выходной)
            }
        }
    }
}

// =============================
// Обработка аргументов скрипта
// =============================
$year = $argv[1] ?? date('Y');
$month = $argv[2] ?? date('n');
$monthsCount = $argv[3] ?? 1;

setlocale(LC_TIME, 'ru_RU.UTF-8'); // русские названия месяцев (Linux/macOS)
// Для Windows может быть: setlocale(LC_TIME, 'Russian');

generateSchedule((int)$year, (int)$month, (int)$monthsCount);