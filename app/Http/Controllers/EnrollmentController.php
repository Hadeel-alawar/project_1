<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Course;
use App\Models\Admin;
use App\ResponseTrait;
use Illuminate\Support\Facades\DB;
use App\Models\Wallet;

class EnrollmentController extends Controller
{
    use ResponseTrait;
    public function enrollInCourse(Request $request)
    {
        $student = auth('api-student')->user();

        $request->validate([
            'course_id' => 'required|exists:courses,id',
        ]);

        $course = Course::findOrFail($request->course_id);

        if ($student->courses()->where('course_id', $course->id)->exists()) {
            return $this->returnError("you can't .. you have just registered in this course .");
        }

        $studentWallet = Wallet::where('owner_id', $student->id)
            ->where('owner_type', 'App\Models\Student')
            ->first();

        if (!$studentWallet) {
            return $this->returnError("student wallet is not found");
        }

        $teacherWallet = Wallet::where('owner_id', $course->teacher_id)
            ->where('owner_type', 'App\Models\Teacher')
            ->first();

        if (!$teacherWallet) {
            return $this->returnError("teacher wallet is not found");
        }

        if ($studentWallet->balance < $course->price) {
            return $this->returnError("the balance is not enough");
        }

        $total = $course->price;
        $teacherShare = $total * 0.7;
        $adminShare = $total * 0.3;

        $studentWallet->balance -= $total;
        $studentWallet->save();

        $teacherWallet->balance += $teacherShare;
        $teacherWallet->save();

        Transaction::create([
            'sender_wallet_id' => $studentWallet->id,
            'receiver_wallet_id' => $teacherWallet->id,
            'amount' => $teacherShare,
            'description' => 'paying for course enrollment : ' . $course->title,
        ]);

        $adminWallet = Wallet::where('owner_type', 'App\Models\Admin')->first();

        if (!$adminWallet) {
            return $this->returnError("admin wallet is not found");
        }

        $adminWallet->balance += $adminShare;
        $adminWallet->save();

        Transaction::create([
            'sender_wallet_id' => $studentWallet->id,
            'receiver_wallet_id' => $adminWallet->id,
            'amount' => $adminShare,
            'description' => 'admin commission from course: ' . $course->title,
        ]);


        $student->courses()->attach($course->id, [
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $this->returnSuccess("payment is done successfully");
    }
}
