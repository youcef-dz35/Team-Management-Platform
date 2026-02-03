<?php

namespace App\Notifications;

use App\Models\ConflictAlert;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ConflictEscalatedNotification extends Notification implements ShouldQueue
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
        $daysOpen = $this->conflictAlert->created_at->diffInDays(now());

        return (new MailMessage)
            ->subject('ESCALATED: Unresolved Conflict Alert')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line("A conflict alert has been **escalated** after remaining unresolved for {$daysOpen} days.")
            ->line('---')
            ->line("**Employee:** {$employee->name}")
            ->line("**Reporting Period:** {$periodStart} - {$periodEnd}")
            ->line("**Source A Hours (Project Reports):** {$this->conflictAlert->source_a_hours}")
            ->line("**Source B Hours (Department Reports):** {$this->conflictAlert->source_b_hours}")
            ->line("**Discrepancy:** {$discrepancy} hours")
            ->line('---')
            ->action('Review and Resolve', url('/conflicts/' . $this->conflictAlert->id))
            ->line('This conflict requires immediate executive attention.')
            ->salutation('Team Management Platform');
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
            'days_open' => $this->conflictAlert->created_at->diffInDays(now()),
            'type' => 'conflict_escalated',
            'message' => 'Conflict alert escalated for ' . ($this->conflictAlert->employee->name ?? 'an employee'),
        ];
    }
}
