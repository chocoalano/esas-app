<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::unprepared("
            CREATE PROCEDURE UpdateAttendanceIn(
                IN p_user_id INT,
                IN p_time_id INT,
                IN p_lat DECIMAL(10, 8),
                IN p_long DECIMAL(11, 8),
                IN p_image VARCHAR(255),
                IN p_time TIME
            )
            BEGIN
                DECLARE v_attendance_id INT DEFAULT NULL;
                DECLARE v_schedule_id INT DEFAULT NULL;
                DECLARE v_in_time TIME;
                DECLARE v_status VARCHAR(10);
                DECLARE exit_code INT DEFAULT 0; -- Variabel untuk menandakan status eksekusi
                -- Start a transaction
                START TRANSACTION;
                -- Cek apakah semua field yang diperlukan ada
                IF p_time_id IS NULL OR p_lat IS NULL OR p_long IS NULL OR p_image IS NULL OR p_time IS NULL THEN
                    SET exit_code = 0; -- Set exit code to false
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Required fields are missing';
                END IF;
                -- Mendapatkan attendance berdasarkan user dan waktu masuk
                SELECT id INTO v_attendance_id
                FROM user_attendances
                WHERE user_id = p_user_id
                AND DATE(created_at) = CURDATE()
                LIMIT 1;
                -- Dapatkan jadwal kerja sesuai waktu dan hari
                SELECT id INTO v_schedule_id
                FROM user_timework_schedules
                WHERE user_id = p_user_id
                AND work_day = CURDATE()
                AND time_work_id = p_time_id
                LIMIT 1;
                -- Dapatkan waktu masuk dari time_work
                SELECT `in` INTO v_in_time
                FROM time_workes
                WHERE id = p_time_id
                LIMIT 1;
                -- Jika waktu masuk tidak ditemukan, beri pesan error
                IF v_in_time IS NULL THEN
                    SET exit_code = 0; -- Set exit code to false
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Invalid time_id: Time In not found';
                END IF;
                -- Cek status (late atau normal)
                SET v_status = IF(p_time > v_in_time, 'late', 'normal');
                -- Jika attendance tidak ditemukan, buat baru
                IF v_attendance_id IS NULL THEN
                    -- Insert data attendance baru
                    INSERT INTO user_attendances (
                        user_id,
                        user_timework_schedule_id,
                        time_in,
                        lat_in,
                        long_in,
                        image_in,
                        status_in,
                        created_at
                    )
                    VALUES (
                        p_user_id,
                        v_schedule_id,
                        p_time,
                        p_lat,
                        p_long,
                        p_image,
                        v_status,
                        CURRENT_TIMESTAMP()
                    );
                ELSE
                    -- Perbarui data attendance yang ada
                    UPDATE user_attendances
                    SET
                        user_timework_schedule_id = v_schedule_id,
                        time_in = p_time,
                        lat_in = p_lat,
                        long_in = p_long,
                        image_in = p_image,
                        status_in = v_status,
                        updated_at = CURRENT_TIMESTAMP()
                    WHERE id = v_attendance_id;
                END IF;
                -- Commit the transaction
                COMMIT;
                -- Set exit code to true
                SET exit_code = 1; -- Set exit code to true
                -- Return the exit code
                SELECT exit_code AS success;
            END;
        ");
        // Mengganti delimiter untuk menghindari konflik dengan ";" di dalam prosedur
        DB::unprepared("
            CREATE PROCEDURE UpdateAttendanceOut(
                IN p_user_id INT,
                IN p_time_id INT,
                IN p_lat DECIMAL(10, 8),
                IN p_long DECIMAL(11, 8),
                IN p_image VARCHAR(255),
                IN p_time TIME
            )
            BEGIN
                DECLARE v_attendance_id INT DEFAULT NULL;
                DECLARE v_image_out VARCHAR(255);
                DECLARE v_out_time TIME;
                DECLARE v_status VARCHAR(10);
                DECLARE exit_code INT DEFAULT 0; -- Variabel untuk menandakan status eksekusi
                -- Start a transaction
                START TRANSACTION;
                -- Validate required fields
                IF p_user_id IS NULL OR p_time_id IS NULL OR p_lat IS NULL OR p_long IS NULL OR p_image IS NULL OR p_time IS NULL THEN
                    SET exit_code = 0; -- Set exit code to false
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Missing required input parameters';
                END IF;
                -- Retrieve attendance record for the user (check if attendance exists)
                SELECT id, image_out INTO v_attendance_id, v_image_out
                FROM user_attendances
                WHERE user_id = p_user_id
                AND time_in IS NOT NULL
                AND DATE(created_at) = CURDATE()
                LIMIT 1;
                -- Check if the attendance record exists
                IF v_attendance_id IS NULL THEN
                    SET exit_code = 0; -- Set exit code to false
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Attendance not found for the given user and date';
                END IF;
                -- Retrieve the scheduled 'out' time from time_work
                SELECT `out` INTO v_out_time
                FROM time_workes
                WHERE id = p_time_id
                LIMIT 1;
                -- Ensure 'out' time is valid
                IF v_out_time IS NULL THEN
                    SET exit_code = 0; -- Set exit code to false
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Out time not found for the specified time ID';
                END IF;
                -- Determine the status based on the comparison of 'out' time and provided 'p_time'
                IF p_time < v_out_time THEN
                    SET v_status = 'normal';
                ELSE
                    SET v_status = 'unlate';
                END IF;
                -- Update attendance record with time out and status
                UPDATE user_attendances
                SET
                    time_out = p_time,
                    lat_out = p_lat,
                    long_out = p_long,
                    image_out = p_image,
                    status_out = v_status,
                    updated_at = CURRENT_TIMESTAMP()
                WHERE id = v_attendance_id;
                -- Commit the transaction
                COMMIT;
                -- Set exit code to true
                SET exit_code = 1; -- Set exit code to true
                -- Return the exit code
                SELECT exit_code AS success;
            END;
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS UpdateAttendanceIn");
        DB::unprepared("DROP PROCEDURE IF EXISTS UpdateAttendanceOut");
    }
};
