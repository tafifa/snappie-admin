<?php

// namespace App\Services;

// use App\Models\User;
// use Illuminate\Support\Facades\Mail;
// use Illuminate\Support\Facades\Log;
// use Exception;

// class NotificationService
// {
//     /**
//      * Send email notification
//      *
//      * @param User $user
//      * @param string $subject
//      * @param string $message
//      * @param array $data Additional data for email template
//      * @return bool
//      */
//     public function sendEmail(User $user, string $subject, string $message, array $data = []): bool
//     {
//         try {
//             Mail::send('emails.notification', array_merge([
//                 'user' => $user,
//                 'message' => $message
//             ], $data), function ($mail) use ($user, $subject) {
//                 $mail->to($user->email, $user->name)
//                      ->subject($subject);
//             });

//             Log::info("Email sent to user {$user->id}: {$subject}");
//             return true;
//         } catch (Exception $e) {
//             Log::error("Failed to send email to user {$user->id}: " . $e->getMessage());
//             return false;
//         }
//     }

//     /**
//      * Send welcome email to new user
//      *
//      * @param User $user
//      * @return bool
//      */
//     public function sendWelcomeEmail(User $user): bool
//     {
//         $subject = 'Welcome to Snappie!';
//         $message = "Hi {$user->name}, welcome to Snappie! Start exploring amazing places and earn rewards.";
        
//         return $this->sendEmail($user, $subject, $message, [
//             'welcome' => true,
//             'app_name' => config('app.name')
//         ]);
//     }

//     /**
//      * Send password reset email
//      *
//      * @param User $user
//      * @param string $resetToken
//      * @return bool
//      */
//     public function sendPasswordResetEmail(User $user, string $resetToken): bool
//     {
//         $subject = 'Password Reset Request';
//         $message = "Hi {$user->name}, you requested a password reset. Use the token below to reset your password.";
        
//         return $this->sendEmail($user, $subject, $message, [
//             'reset_token' => $resetToken,
//             'reset_url' => url("/reset-password?token={$resetToken}")
//         ]);
//     }

//     /**
//      * Send achievement notification
//      *
//      * @param User $user
//      * @param string $achievementName
//      * @param int $expReward
//      * @param int $coinReward
//      * @return bool
//      */
//     public function sendAchievementNotification(User $user, string $achievementName, int $expReward = 0, int $coinReward = 0): bool
//     {
//         $subject = 'New Achievement Unlocked!';
//         $message = "Congratulations {$user->name}! You've unlocked the '{$achievementName}' achievement.";
        
//         return $this->sendEmail($user, $subject, $message, [
//             'achievement_name' => $achievementName,
//             'exp_reward' => $expReward,
//             'coin_reward' => $coinReward
//         ]);
//     }

//     /**
//      * Send level up notification
//      *
//      * @param User $user
//      * @param int $newLevel
//      * @return bool
//      */
//     public function sendLevelUpNotification(User $user, int $newLevel): bool
//     {
//         $subject = 'Level Up!';
//         $message = "Amazing {$user->name}! You've reached level {$newLevel}. Keep exploring to unlock more rewards!";
        
//         return $this->sendEmail($user, $subject, $message, [
//             'new_level' => $newLevel,
//             'level_up' => true
//         ]);
//     }

//     /**
//      * Send reward notification
//      *
//      * @param User $user
//      * @param string $rewardName
//      * @param string $rewardType
//      * @return bool
//      */
//     public function sendRewardNotification(User $user, string $rewardName, string $rewardType = 'reward'): bool
//     {
//         $subject = 'New Reward Available!';
//         $message = "Great news {$user->name}! You've earned a new reward: {$rewardName}";
        
//         return $this->sendEmail($user, $subject, $message, [
//             'reward_name' => $rewardName,
//             'reward_type' => $rewardType
//         ]);
//     }

//     /**
//      * Send challenge completion notification
//      *
//      * @param User $user
//      * @param string $challengeName
//      * @param int $expReward
//      * @param int $coinReward
//      * @return bool
//      */
//     public function sendChallengeCompletionNotification(User $user, string $challengeName, int $expReward = 0, int $coinReward = 0): bool
//     {
//         $subject = 'Challenge Completed!';
//         $message = "Excellent work {$user->name}! You've completed the '{$challengeName}' challenge.";
        
//         return $this->sendEmail($user, $subject, $message, [
//             'challenge_name' => $challengeName,
//             'exp_reward' => $expReward,
//             'coin_reward' => $coinReward,
//             'challenge_completed' => true
//         ]);
//     }

//     /**
//      * Send bulk notification to multiple users
//      *
//      * @param array $users
//      * @param string $subject
//      * @param string $message
//      * @param array $data
//      * @return array
//      */
//     public function sendBulkNotification(array $users, string $subject, string $message, array $data = []): array
//     {
//         $results = [];

//         foreach ($users as $user) {
//             if ($user instanceof User) {
//                 $results[$user->id] = $this->sendEmail($user, $subject, $message, $data);
//             }
//         }

//         return $results;
//     }

//     /**
//      * Send push notification (placeholder for future implementation)
//      *
//      * @param User $user
//      * @param string $title
//      * @param string $body
//      * @param array $data
//      * @return bool
//      */
//     public function sendPushNotification(User $user, string $title, string $body, array $data = []): bool
//     {
//         // TODO: Implement push notification service (Firebase, OneSignal, etc.)
//         Log::info("Push notification would be sent to user {$user->id}: {$title}");
//         return true;
//     }

//     /**
//      * Send SMS notification (placeholder for future implementation)
//      *
//      * @param User $user
//      * @param string $message
//      * @return bool
//      */
//     public function sendSMS(User $user, string $message): bool
//     {
//         // TODO: Implement SMS service (Twilio, etc.)
//         if (!$user->phone) {
//             Log::warning("Cannot send SMS to user {$user->id}: No phone number");
//             return false;
//         }

//         Log::info("SMS would be sent to user {$user->id} at {$user->phone}: {$message}");
//         return true;
//     }

//     /**
//      * Send notification based on user preferences
//      *
//      * @param User $user
//      * @param string $type
//      * @param string $title
//      * @param string $message
//      * @param array $data
//      * @return array
//      */
//     public function sendNotificationByPreference(User $user, string $type, string $title, string $message, array $data = []): array
//     {
//         $results = [];

//         // Always try email first
//         $results['email'] = $this->sendEmail($user, $title, $message, $data);

//         // Send push notification if user has enabled it
//         // TODO: Check user notification preferences from database
//         $results['push'] = $this->sendPushNotification($user, $title, $message, $data);

//         // Send SMS for critical notifications only
//         if (in_array($type, ['security', 'password_reset', 'account_locked'])) {
//             $results['sms'] = $this->sendSMS($user, $message);
//         }

//         return $results;
//     }

//     /**
//      * Log notification activity
//      *
//      * @param User $user
//      * @param string $type
//      * @param string $subject
//      * @param bool $success
//      * @return void
//      */
//     private function logNotification(User $user, string $type, string $subject, bool $success): void
//     {
//         $status = $success ? 'sent' : 'failed';
//         Log::info("Notification {$status} - User: {$user->id}, Type: {$type}, Subject: {$subject}");
//     }
// }