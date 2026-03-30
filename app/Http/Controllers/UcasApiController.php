<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Student;
use App\Models\Schedule;

class UcasApiController extends Controller
{
    public function login(Request $request)
    {
    
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $username = $request->username;
        $password = $request->password;

        
        $loginResponse = Http::asForm()->post('https://quiztoxml.ucas.edu.ps/api/login', [
            'username' => $username,
            'password' => $password,
        ]);

        $loginData = $loginResponse->json();

        
        if (!isset($loginData['success']) || $loginData['success'] === false) {
            return response()->json([
                'error' => 'كلمة المرور او اسم المستخدم خطا'
            ], 401);
        }

    
        $studentName = $loginData['data']['user_ar_name'] ?? ($loginData['data']['user_en_name'] ?? 'Unknown User');
        $token = $loginData['Token'] ?? ($loginData['token'] ?? null);

        if (!$token) {
            return response()->json([
                'error' => 'لم يتم العثور على التوكن في الرد.',
                'raw_response' => $loginData
            ], 500);
        }

        $student = Student::updateOrCreate(
            ['student_id' => $username],
            [
                'name' => $studentName,
                'token' => $token
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل الدخول بنجاح',
            'student' => $student,
        ]);
    }

    public function fetch_table(Request $request)
    {
        
        $request->validate([
            'user_id' => 'required',
            'token' => 'required',
        ]);

        $userId = $request->user_id;
        $token = $request->token;

        
        $tableResponse = Http::asForm()->post('https://quiztoxml.ucas.edu.ps/api/get-table', [
            'user_id' => $userId,
            'token' => $token,
        ]);

        $tableData = $tableResponse->json();

        if (!isset($tableData['success']) || $tableData['success'] === false) {
            return response()->json([
                'error' => 'التوكن غير صحيح',
                'raw_response' => $tableData
            ], 401);
        }

        Schedule::where('student_id', $userId)->delete();

      
        if (isset($tableData['data']) && is_array($tableData['data'])) {
            $items = $tableData['data'];
        } elseif (is_array($tableData)) {
            $items = $tableData;
        } else {
            $items = [];
        }

        foreach ($items as $item) {
            if (!is_array($item)) continue;
            
            Schedule::create([
                'student_id' => $userId,
                
                'course_code' => $item['subject_no'] ?? $item['course_no'] ?? null,
                'course_name' => $item['subject_name'] ?? $item['course_name'] ?? null,
                'instructor' => $item['teacher_name'] ?? $item['instructor'] ?? null,
                'days' => $item['days'] ?? $item['day'] ?? null,
                'time' => (isset($item['time_from']) && isset($item['time_to'])) ? ($item['time_from'] . ' - ' . $item['time_to']) : ($item['time'] ?? null),
                'room' => $item['room_no'] ?? $item['room'] ?? null,
                'raw_data' => $item,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم جلب الجدول وحفظ البيانات بنجاح',
            'raw_response' => $tableData,
            'schedule' => Schedule::where('student_id', $userId)->get()
        ]);
    }
}
