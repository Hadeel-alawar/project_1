<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Course;
use App\Models\Admin;
use App\ResponseTrait;
use Illuminate\Support\Facades\DB;

class EnrollmentController extends Controller
{
    use ResponseTrait;
    public function enrollInCourse(Request $request, $courseId)
    {
        $student = auth('api-student')->user();
        $course = Course::findOrFail($courseId);
        $teacher = $course->teacher;
        $admin = Admin::first(); // تأكد من وجود Admin دائمًا أو عبر علاقة

        // المحافظ
        $studentWallet = $student->wallet;
        $teacherWallet = $teacher->wallet;
        $adminWallet = $admin->wallet;

        // السعر
        $price = $course->price;
        if ($studentWallet->balance < $price) {
            return $this->returnError("Your wallet balance is not sufficient.");
        }

        // النسب
        $teacherAmount = $price * 0.9;
        $adminAmount = $price * 0.1;

        // العمليات
        DB::beginTransaction();
        try {
            // خصم من الطالب
            $studentWallet->decrement('balance', $price);

            // تحويل 90% إلى الأستاذ
            $teacherWallet->increment('balance', $teacherAmount);
            Transaction::create([
                'sender_wallet_id' => $studentWallet->id,
                'receiver_wallet_id' => $teacherWallet->id,
                'amount' => $teacherAmount,
                'description' => "Course purchase - teacher share"
            ]);

            // تحويل 10% إلى الأدمن
            $adminWallet->increment('balance', $adminAmount);
            Transaction::create([
                'sender_wallet_id' => $studentWallet->id,
                'receiver_wallet_id' => $adminWallet->id,
                'amount' => $adminAmount,
                'description' => "Course purchase - admin share"
            ]);

            // إضافة اشتراك الطالب في الدورة
            $student->courses()->attach($courseId);

            DB::commit();
            return $this->returnSuccess("Enrolled in course successfully.");
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->returnError("Payment failed: " . $e->getMessage());
        }
    }
}
