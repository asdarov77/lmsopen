<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'fio' => 'Администратор',
            'name' => 'Администратор',
            'email' => 'admin@example.com',
            'password' => '123',
            'role' => 'Администратор',
            'group_id' => 1,
        ]);

        User::create([
            'fio' => 'Иванов Иван Иванович',
            'name' => 'Иванов Иван Иванович',
            'email' => 'ivanov@example.com',
            'password' => '123',
            'role' => 'Инструктор',
            'group_id' => 1,
        ]);

        User::create([
            'fio' => 'Петров Иван Иванович',
            'name' => 'Петров Иван Иванович',
            'email' => 'petrov@example.com',
            'password' => '123',
            'role' => 'Обучаемый',
            'group_id' => 1,
        ]);

        User::create([
            'fio' => 'Сидоров Иван Иванович',
            'name' => 'Сидоров Иван Иванович',
            'email' => 'sidorov@example.com',
            'password' => '123',
            'role' => 'Обучаемый',
            'group_id' => 1,
        ]);

        User::create([
            'fio' => 'Корнеев Иван Иванович',
            'name' => 'Корнеев Иван Иванович',
            'email' => 'korneev@example.com',
            'password' => '123',
            'role' => 'Обучаемый',
            'group_id' => 1,
        ]);

        User::create([
            'fio' => 'Семенов Иван Иванович',
            'name' => 'Семенов Иван Иванович',
            'email' => 'semenov@example.com',
            'password' => '123',
            'role' => 'Обучаемый',
            'group_id' => 1,
        ]);

        User::create([
            'fio' => 'Крупнов Максим Витальевич',
            'name' => 'Крупнов Максим Витальевич',
            'email' => 'krupnov@example.com',
            'password' => '123',
            'role' => 'Обучаемый',
            'group_id' => 1,
        ]);

        User::create([
            'fio' => 'Ненадович Иван Иванович',
            'name' => 'Ненадович Иван Иванович',
            'email' => 'nenadovich@example.com',
            'password' => '123',
            'role' => 'Обучаемый',
            'group_id' => 1,
        ]);

        User::create([
            'fio' => 'Пименов Иван Иванович',
            'name' => 'Пименов Иван Иванович',
            'email' => 'pimenov@example.com',
            'password' => '123',
            'role' => 'Инструктор',
            'group_id' => 1,
        ]);

        User::create([
            'fio' => 'Пельш Иван Иванович',
            'name' => 'Пельш Иван Иванович',
            'email' => 'pelsh@example.com',
            'password' => '123',
            'role' => 'Обучаемый',
            'group_id' => 1,
        ]);

        User::create([
            'fio' => 'Моисеев Иван Иванович',
            'name' => 'Моисеев Иван Иванович',
            'email' => 'moiseev@example.com',
            'password' => '123',
            'role' => 'Обучаемый',
            'group_id' => 2,
        ]);

        User::create([
            'fio' => 'Рабинович Хаим Иванович',
            'name' => 'Рабинович Хаим Иванович',
            'email' => 'rabinovich@example.com',
            'password' => '123',
            'role' => 'Обучаемый',
            'group_id' => 2,
        ]);

        User::create([
            'fio' => 'Медведев Дмитрий Анатольевич',
            'name' => 'Медведев Дмитрий Анатольевич',
            'email' => 'medvedev@example.com',
            'password' => '123',
            'role' => 'Обучаемый',
            'group_id' => 2,
        ]);

        User::create([
            'fio' => 'Трунов Иван Семенович',
            'name' => 'Трунов Иван Семенович',
            'email' => 'trunov@example.com',
            'password' => '123',
            'role' => 'Обучаемый',
            'group_id' => 2,
        ]);

        User::create([
            'fio' => 'Селезнев Тимофей Михайлович',
            'name' => 'Селезнев Тимофей Михайлович',
            'email' => 'seleznev@example.com',
            'password' => '123',
            'role' => 'Инструктор',
            'group_id' => 2,
        ]);

        User::create([
            'fio' => 'Нагиев Дмитрий Владимирович',
            'name' => 'Нагиев Дмитрий Владимирович',
            'email' => 'nagiev@example.com',
            'password' => '123',
            'role' => 'Обучаемый',
            'group_id' => 3,
        ]);

        User::create([
            'fio' => 'Шойгу Сергей Кужугетович',
            'name' => 'Шойгу Сергей Кужугетович',
            'email' => 'shoygu@example.com',
            'password' => '123',
            'role' => 'Обучаемый',
            'group_id' => 3,
        ]);

        User::create([
            'fio' => 'Великов Вадим Иванович',
            'name' => 'Великов Вадим Иванович',
            'email' => 'velikov@example.com',
            'password' => '123',
            'role' => 'Обучаемый',
            'group_id' => 3,
        ]);

        User::create([
            'fio' => 'Фурсов Андрей Ильич',
            'name' => 'Фурсов Андрей Ильич',
            'email' => 'fursov@example.com',
            'password' => '123',
            'role' => 'Обучаемый',
            'group_id' => 3,
        ]);

        User::create([
            'fio' => 'Савельев Сергей Вячеславович',
            'name' => 'Савельев Сергей Вячеславович',
            'email' => 'saveliev@example.com',
            'password' => '123',
            'role' => 'Обучаемый',
            'group_id' => 3,
        ]);

        User::create([
            'fio' => 'Панчин Александр Юрьевич',
            'name' => 'Панчин Александр Юрьевич',
            'email' => 'panchin@example.com',
            'password' => '123',
            'role' => 'Обучаемый',
            'group_id' => 3,
        ]);
    }
}
