<?php
/**
 * Генератор расписания работы (сутки через двое)
 */
function generateWorkSchedule(int $year, int $month): array {
    $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
    $schedule = [];
    $workDayCounter = 0;
    $restDays = 0;

    for ($day = 1; $day <= $daysInMonth; $day++) {
        $currentDate = new DateTime("$year-$month-$day");
        $dayOfWeek = (int)$currentDate->format('N'); // 1-пн, 7-вс
        $isWorkDay = false;

        // Первый день месяца - всегда рабочий
        if ($day === 1) {
            $isWorkDay = true;
            $workDayCounter = 1;
            $restDays = 0;
        } else {
            if ($workDayCounter === 1) {
                // После рабочего дня идёт 2 выходных
                if ($restDays < 2) {
                    $restDays++;
                    $isWorkDay = false;
                } else {
                    $isWorkDay = true;
                    $workDayCounter = 1;
                    $restDays = 0;
                }
            } else {
                // Проверяем, не нужно ли перенести рабочий день с выходных на понедельник
                if ($restDays === 2) {
                    if ($dayOfWeek >= 6) { // Если выпадает на выходные
                        // Пропускаем выходные и ставим рабочий день в понедельник
                        continue;
                    } else {
                        $isWorkDay = true;
                        $workDayCounter = 1;
                        $restDays = 0;
                    }
                }
            }
        }

        // Если это понедельник после переноса
        if ($dayOfWeek === 1 && $day > 1) {
            $prevDay = clone $currentDate;
            $prevDay->modify('-1 day');
            $prevDayNumber = (int)$prevDay->format('j');
            
            // Проверяем, был ли предыдущий день воскресеньем и рабочим днём
            if (isset($schedule[$prevDayNumber]) && $schedule[$prevDayNumber] === false) {
                $isWorkDay = true;
                $workDayCounter = 1;
                $restDays = 0;
                
                // Отменяем предыдущий рабочий день (если был запланирован на выходные)
                for ($i = $prevDayNumber; $i >= 1; $i--) {
                    if ($schedule[$i] === true) {
                        $schedule[$i] = false;
                        break;
                    }
                }
            }
        }

        $schedule[$day] = $isWorkDay;
    }

    return $schedule;
}

/**
 * Вывод расписания на экран
 */
function printSchedule(int $year, int $month): void {
    $monthNames = [
        1 => 'Январь', 2 => 'Февраль', 3 => 'Март', 4 => 'Апрель',
        5 => 'Май', 6 => 'Июнь', 7 => 'Июль', 8 => 'Август',
        9 => 'Сентябрь', 10 => 'Октябрь', 11 => 'Ноябрь', 12 => 'Декабрь'
    ];
    
    $schedule = generateWorkSchedule($year, $month);
    $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

    echo "\n\033[1m" . $monthNames[$month] . " $year года\033[0m\n";
    echo "Пн Вт Ср Чт Пт \033[31mСб Вс\033[0m\n";

    $firstDay = (new DateTime("$year-$month-01"))->format('N');
    echo str_repeat('   ', $firstDay - 1);

    for ($day = 1; $day <= $daysInMonth; $day++) {
        $currentDate = new DateTime("$year-$month-$day");
        $dayOfWeek = (int)$currentDate->format('N');
        
        $output = sprintf("%2d", $day);
        if ($schedule[$day]) {
            $output = "\033[32m" . $output . "+\033[0m";
        } elseif ($dayOfWeek >= 6) {
            $output = "\033[31m" . $output . "\033[0m";
        }

        echo $output . ' ';
        
        if ($dayOfWeek === 7) {
            echo "\n";
        }
    }
    echo "\n";
}

// Обработка аргументов командной строки
$year = date('Y');
$month = date('m');
$monthsCount = 1;

if ($argc > 1) {
    if ($argc >= 3) {
        $year = (int)$argv[1];
        $month = (int)$argv[2];
        if ($argc >= 4) {
            $monthsCount = (int)$argv[3];
        }
    } else {
        echo "Использование: php schedule.php [год месяц [количество_месяцев]]\n";
        exit(1);
    }
}

// Вывод расписания для указанного периода
for ($i = 0; $i < $monthsCount; $i++) {
    $currentMonth = $month + $i;
    $currentYear = $year;
    
    if ($currentMonth > 12) {
        $currentMonth -= 12;
        $currentYear++;
    }
    
    printSchedule($currentYear, $currentMonth);
}
?>