<?php
// Генератор расписания "сутки через двое"
// Использование: php schedule.php [год] [месяц] [количество_месяцев]

function generateSchedule($year, $month, $monthsCount = 1) {
    $workShift = true; // первое число месяца — рабочий день

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

        $day = 1;
        while ($day <= $daysInMonth) {
            $date = mktime(0, 0, 0, $currentMonth, $day, $currentYear);
            $weekday = date('N', $date); // 1=Пн ... 7=Вс
            $isWorkDay = false;

            if ($workShift) {
                // Если выпадает на субботу (6) или воскресенье (7), перенос на понедельник
                if ($weekday == 6 || $weekday == 7) {
                    $nextMonday = $day + (8 - $weekday);
                    if ($nextMonday <= $daysInMonth) {
                        $day = $nextMonday;
                        $date = mktime(0, 0, 0, $currentMonth, $day, $currentYear);
                        $isWorkDay = true;
                        $weekday = date('N', $date);
                    } else {
                        // если перенос выходит за пределы месяца — пропускаем
                        $isWorkDay = false;
                    }
                } else {
                    $isWorkDay = true;
                }
            }

            // Выводим день
            $formatted = date('d.m.Y (D)', $date);
            if ($isWorkDay) {
                echo "\033[32m+ $formatted\033[0m\n"; // зелёный
            } else {
                echo "  $formatted\n";
            }

            // Логика смены: 1 рабочий -> 2 выходных -> снова рабочий
            if ($isWorkDay) {
                $workShift = false;
                $skip = 2; // пропускаем два дня
                $day += $skip;
                $workShift = true;
            } else {
                $day++;
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
// В Windows может быть: setlocale(LC_TIME, 'Russian');

generateSchedule((int)$year, (int)$month, (int)$monthsCount);