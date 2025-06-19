<?php

namespace App\Http\Controllers;

use App\Models\{Patient, Department, User};
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Log};
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Get dashboard statistics including today's patient counts and system alerts.
     *
     * @return JsonResponse
     */
    public function getStats(): JsonResponse
    {
        try {
            $user = auth()->user();
            $today = Carbon::now()->toDateString();
            
            // Get user's accessible departments
            $departmentIds = $user->getAllDepartmentIds();
            $isSuperAdmin = $user->role_id == 1;

            // Build base query for today's patients
            $baseQuery = Patient::whereDate('created_at', $today);
            
            // Apply department filtering for non-superadmin users
            if (!$isSuperAdmin) {
                $baseQuery->where(function($q) use ($departmentIds) {
                    $q->whereIn('starting_department_id', $departmentIds)
                      ->orWhereIn('next_department_id', $departmentIds);
                });
            }

            // Get today's statistics
            $todayStats = [
                'totalPatients' => (clone $baseQuery)->count(),
                'waitingPatients' => (clone $baseQuery)->where('status', 'waiting')->count(),
                'inProgressPatients' => (clone $baseQuery)->where('status', 'in-progress')->count(),
                'completedPatients' => (clone $baseQuery)->where('status', 'completed')->count(),
            ];

            // Get system alerts
            $alerts = $this->generateSystemAlerts($todayStats, $departmentIds, $isSuperAdmin);

            return response()->json([
                'todayStats' => $todayStats,
                'alerts' => []
            ]);

        } catch (\Exception $e) {
            Log::error("Error fetching dashboard stats: " . $e->getMessage());
            return response()->json(['message' => 'Failed to fetch dashboard statistics.'], 500);
        }
    }

    /**
     * Get recent patient activity for the dashboard.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getRecentActivity(Request $request): JsonResponse
    {
        try {
            $user = auth()->user();
            $departmentIds = $user->getAllDepartmentIds();
            $isSuperAdmin = $user->role_id == 1;
            
            // Get limit from request (default 20, max 50)
            $limit = min($request->get('limit', 20), 50);

            // Build query for recent activities
            $query = Patient::with(['starting_department', 'next_department'])
                           ->whereNotNull('session_started')
                           ->orderBy('session_started', 'desc');

            // Apply department filtering for non-superadmin users
            if (!$isSuperAdmin) {
                $query->where(function($q) use ($departmentIds) {
                    $q->whereIn('starting_department_id', $departmentIds)
                      ->orWhereIn('next_department_id', $departmentIds);
                });
            }

            $recentPatients = $query->limit($limit)->get();

            // Transform the data for the frontend
            $activities = $recentPatients->map(function ($patient) {
                // Determine the most recent activity
                $timestamp = $patient->session_ended ?? $patient->session_started;
                $type = $patient->status;
                
                // Get department name (prefer next_department, fallback to starting_department)
                $department = $patient->next_department 
                    ? $patient->next_department->name 
                    : ($patient->starting_department ? $patient->starting_department->name : 'Unknown');

                return [
                    'patient_name' => $patient->name ?? 'Patient ' . $patient->priority . str_pad($patient->priority_number, 3, '0', STR_PAD_LEFT),
                    'department' => $department,
                    'type' => $type,
                    'timestamp' => $timestamp,
                    'priority' => $patient->priority,
                    'priority_number' => $patient->priority_number,
                ];
            });

            return response()->json($activities);

        } catch (\Exception $e) {
            Log::error("Error fetching recent activity: " . $e->getMessage());
            return response()->json(['message' => 'Failed to fetch recent activity.'], 500);
        }
    }

    /**
     * Generate system alerts based on current conditions.
     *
     * @param array $todayStats
     * @param array $departmentIds
     * @param bool $isSuperAdmin
     * @return array
     */
    private function generateSystemAlerts(array $todayStats, array $departmentIds, bool $isSuperAdmin): array
    {
        $alerts = [];
        
        try {
            // Alert for high waiting patient count
            if ($todayStats['waitingPatients'] > 20) {
                $alerts[] = [
                    'message' => "High patient load: {$todayStats['waitingPatients']} patients currently waiting - consider additional staffing",
                    'type' => 'warning',
                    'priority' => 'high'
                ];
            }

            // Alert for departments with high patient load
            $departmentQuery = Department::with(['patient'])
                                        ->whereHas('patient', function($q) {
                                            $q->where('status', 'in-progress');
                                        });
            
            if (!$isSuperAdmin) {
                $departmentQuery->whereIn('id', $departmentIds);
            }

            $busyDepartments = $departmentQuery->get()->filter(function($dept) {
                return $dept->patient && $dept->getWaitingPatientsAttribute() > 10;
            });

            foreach ($busyDepartments as $dept) {
                $alerts[] = [
                    'message' => "High patient load in {$dept->name} Department - consider additional resources",
                    'type' => 'warning',
                    'priority' => 'medium'
                ];
            }

            // Alert for system maintenance (example - you can customize this)
            $currentHour = Carbon::now()->hour;
            if ($currentHour >= 14 && $currentHour <= 15) { // 2-3 PM
                $alerts[] = [
                    'message' => "Scheduled system maintenance window: 3:00 PM - 3:30 PM today",
                    'type' => 'info',
                    'priority' => 'low'
                ];
            }

            // Alert for low staff count (if you have staff scheduling data)
            $activeStaffCount = User::where('role_id', 3) // Assuming role_id 3 is staff
                                   ->whereIn('department_id', $isSuperAdmin ? Department::pluck('id') : $departmentIds)
                                   ->count();
            
            if ($activeStaffCount < 5 && $todayStats['waitingPatients'] > 10) {
                $alerts[] = [
                    'message' => "Low staff availability with high patient load - consider calling additional staff",
                    'type' => 'warning',
                    'priority' => 'high'
                ];
            }
        } catch (\Exception $e) {
            Log::error("Error generating system alerts: " . $e->getMessage());
            // Don't fail the entire request if alerts fail
        }

        return $alerts;
    }

    /**
     * Get department statistics with current patient counts.
     * This enhances the existing departments endpoint with real-time patient data.
     *
     * @return JsonResponse
     */
    public function getDepartmentStats(): JsonResponse
    {
        try {
            $user = auth()->user();
            $departmentIds = $user->getAllDepartmentIds();
            $isSuperAdmin = $user->role_id == 1;
            $today = Carbon::now()->toDateString();

            // Get departments with patient counts
            $departmentQuery = Department::query();
            
            if (!$isSuperAdmin) {
                $departmentQuery->whereIn('id', $departmentIds);
            }

            $departments = $departmentQuery->get()->map(function ($department) use ($today) {
                // Get current patient counts for this department
                $waitingCount = Patient::where('next_department_id', $department->id)
                                      ->where('status', 'waiting')
                                      ->whereDate('created_at', $today)
                                      ->count();

                $activeCount = Patient::where('next_department_id', $department->id)
                                     ->where('status', 'in-progress')
                                     ->whereDate('created_at', $today)
                                     ->count();

                $completedCount = Patient::where(function($q) use ($department) {
                                        $q->where('starting_department_id', $department->id)
                                          ->orWhere('next_department_id', $department->id)
                                          ->orWhereJsonContains('prev_department_ids', $department->id);
                                    })
                                    ->where('status', 'completed')
                                    ->whereDate('created_at', $today)
                                    ->count();

                return [
                    'id' => $department->id,
                    'name' => $department->name,
                    'description' => $department->description,
                    'current_patients' => $waitingCount + $activeCount,
                    'waiting_patients' => $waitingCount,
                    'active_patients' => $activeCount,
                    'completed_today' => $completedCount,
                    'total_beds' => $department->totalBeds ?? 0,
                    'staff_count' => $department->getStaffCountAttribute(),
                ];
            });

            return response()->json($departments);

        } catch (\Exception $e) {
            Log::error("Error fetching department stats: " . $e->getMessage());
            return response()->json(['message' => 'Failed to fetch department statistics.'], 500);
        }
    }
}
