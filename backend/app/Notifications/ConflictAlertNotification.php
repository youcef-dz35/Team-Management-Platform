<?php

namespace App\Notifications;

use App\Models\ConflictAlert;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ConflictAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected ConflictAlert $conflictAlert;

    /**
     * Create a new notification instance.
     */
    public function __construct(ConflictAlert $conflictAlert)
    {
        $this->conflictAlert = $conflictAlert;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $employee = $this->conflictAlert->employee;
        $discrepancy = abs($this->conflictAlert->discrepancy);
        $periodStart = $this->conflictAlert->reporting_period_start->format('M d, Y');
        $periodEnd = $this->conflictAlert->reporting_period_end->format('M d, Y');

        return (new MailMessage)
            ->subject('New Conflict Alert Detected')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('A new reporting discrepancy has been detected that requires your attention.')
            ->line("**Employee:** {$employee->name}")
            ->line("**Reporting Period:** {$periodStart} - {$periodEnd}")
            ->line("**Source A Hours (Project Reports):** {$this->conflictAlert->source_a_hours}")
            ->line("**Source B Hours (Department Reports):** {$this->conflictAlert->source_b_hours}")
            ->line("**Discrepancy:** {$discrepancy} hours")
            ->action('Review Conflict', url('/conflicts/' . $this->conflictAlert->id))
            ->line('Please review this discrepancy and resolve it within 7 days to prevent escalation to CEO/CFO.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'conflict_alert_id' => $this->conflictAlert->id,
            'employee_id' => $this->conflictAlert->employee_id,
            'employee_name' => $this->conflictAlert->employee->name ?? 'Unknown',
            'source_a_hours' => $this->conflictAlert->source_a_hours,
            'source_b_hours' => $this->conflictAlert->source_b_hours,
            'discrepancy' => $this->conflictAlert->discrepancy,
            'period_start' => $this->conflictAlert->reporting_period_start->format('Y-m-d'),
            'period_end' => $this->conflictAlert->reporting_period_end->format('Y-m-d'),
            'type' => 'conflict_alert',
            'message' => 'New conflict alert detected for ' . ($this->conflictAlert->employee->name ?? 'an employee'),
        ];
    }
}
