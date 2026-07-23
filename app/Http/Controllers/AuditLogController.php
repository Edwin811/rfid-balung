<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $query = AuditLog::with('user')->orderBy('created_at', 'desc');

        if ($search) {
            $query->where('aksi', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%");
                  });
        }

        $logs = $query->paginate(20);

        return view('audit_logs.index', compact('logs', 'search'));
    }
}
