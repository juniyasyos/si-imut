<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Security Stats Widgets -->
        <div>
            {{ $this->getHeaderWidgets() }}
        </div>

        <!-- Blocked IPs Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Blocked IPs -->
            <x-filament::card>
                <x-slot name="heading">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-no-symbol class="w-5 h-5 text-red-500" />
                        Blocked IP Addresses
                    </div>
                </x-slot>

                <div class="space-y-2">
                    @php $blockedIps = $this->getBlockedIps(); @endphp

                    @if(empty($blockedIps))
                        <p class="text-gray-500 text-sm">No IP addresses are currently blocked.</p>
                    @else
                        @foreach($blockedIps as $blockedIp)
                            <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg border border-red-200">
                                <div>
                                    <span class="font-mono text-sm font-medium">{{ $blockedIp['ip'] }}</span>
                                    <p class="text-xs text-gray-600">{{ $blockedIp['reason'] }}</p>
                                </div>
                                <button
                                    wire:click="unblockIp('{{ $blockedIp['ip'] }}')"
                                    class="px-3 py-1 text-xs bg-red-100 hover:bg-red-200 text-red-700 rounded"
                                >
                                    Unblock
                                </button>
                            </div>
                        @endforeach
                    @endif
                </div>
            </x-filament::card>

            <!-- Suspicious IPs -->
            <x-filament::card>
                <x-slot name="heading">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-eye class="w-5 h-5 text-yellow-500" />
                        Suspicious IP Addresses
                    </div>
                </x-slot>

                <div class="space-y-2">
                    @php $suspiciousIps = collect($this->getSuspiciousIps())->take(10); @endphp

                    @if($suspiciousIps->isEmpty())
                        <p class="text-gray-500 text-sm">No suspicious IP addresses detected.</p>
                    @else
                        @foreach($suspiciousIps as $suspiciousIp)
                            <div class="flex items-center justify-between p-3 bg-yellow-50 rounded-lg border border-yellow-200">
                                <div>
                                    <span class="font-mono text-sm font-medium">{{ $suspiciousIp['ip'] }}</span>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="text-xs text-gray-600">
                                            {{ $suspiciousIp['activity_count'] }} events
                                        </span>
                                        <span class="px-2 py-0.5 text-xs rounded-full
                                            @if($suspiciousIp['risk_level'] === 'critical') bg-red-100 text-red-700
                                            @elseif($suspiciousIp['risk_level'] === 'high') bg-orange-100 text-orange-700
                                            @elseif($suspiciousIp['risk_level'] === 'medium') bg-yellow-100 text-yellow-700
                                            @else bg-blue-100 text-blue-700
                                            @endif
                                        ">
                                            {{ ucfirst($suspiciousIp['risk_level']) }}
                                        </span>
                                    </div>
                                </div>
                                @if($suspiciousIp['risk_level'] === 'critical' || $suspiciousIp['risk_level'] === 'high')
                                    <button
                                        wire:click="blockIp('{{ $suspiciousIp['ip'] }}')"
                                        class="px-3 py-1 text-xs bg-red-600 hover:bg-red-700 text-white rounded"
                                    >
                                        Block
                                    </button>
                                @endif
                            </div>
                        @endforeach
                    @endif
                </div>
            </x-filament::card>
        </div>

        <!-- Recent Security Events -->
        <x-filament::card>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-clock class="w-5 h-5 text-blue-500" />
                    Recent Security Events (Last Hour)
                </div>
            </x-slot>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @php $stats = $this->getSecurityStats(); @endphp

                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <div class="text-2xl font-bold text-gray-900">{{ $stats['failed_login'] ?? 0 }}</div>
                    <div class="text-sm text-gray-600">Failed Logins</div>
                </div>

                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <div class="text-2xl font-bold text-red-600">{{ $stats['sql_injection'] ?? 0 }}</div>
                    <div class="text-sm text-gray-600">SQL Injection Attempts</div>
                </div>

                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <div class="text-2xl font-bold text-yellow-600">{{ $stats['rate_limit_exceeded'] ?? 0 }}</div>
                    <div class="text-sm text-gray-600">Rate Limit Violations</div>
                </div>

                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <div class="text-2xl font-bold text-purple-600">{{ $stats['malicious_request'] ?? 0 }}</div>
                    <div class="text-sm text-gray-600">Malicious Requests</div>
                </div>
            </div>
        </x-filament::card>

        <!-- Security Recommendations -->
        <x-filament::card>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-light-bulb class="w-5 h-5 text-green-500" />
                    Security Recommendations
                </div>
            </x-slot>

            <div class="space-y-3">
                @php
                    $stats = $this->getSecurityStats();
                    $recommendations = [];

                    if (($stats['failed_login'] ?? 0) > 50) {
                        $recommendations[] = ['type' => 'warning', 'message' => 'High number of failed login attempts detected. Consider implementing CAPTCHA.'];
                    }

                    if (($stats['sql_injection'] ?? 0) > 0) {
                        $recommendations[] = ['type' => 'danger', 'message' => 'SQL injection attempts detected. Review input validation and database queries.'];
                    }

                    if (count($this->getSuspiciousIps()) > 10) {
                        $recommendations[] = ['type' => 'warning', 'message' => 'Multiple suspicious IPs detected. Consider strengthening access controls.'];
                    }

                    if (($stats['rate_limit_exceeded'] ?? 0) > 100) {
                        $recommendations[] = ['type' => 'info', 'message' => 'High rate limit violations. Review rate limiting thresholds.'];
                    }

                    if (empty($recommendations)) {
                        $recommendations[] = ['type' => 'success', 'message' => 'Security posture looks good! Continue monitoring for anomalies.'];
                    }
                @endphp

                @foreach($recommendations as $recommendation)
                    <div class="flex items-start gap-3 p-3 rounded-lg
                        @if($recommendation['type'] === 'danger') bg-red-50 border border-red-200
                        @elseif($recommendation['type'] === 'warning') bg-yellow-50 border border-yellow-200
                        @elseif($recommendation['type'] === 'info') bg-blue-50 border border-blue-200
                        @else bg-green-50 border border-green-200
                        @endif
                    ">
                        @if($recommendation['type'] === 'danger')
                            <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-red-500 mt-0.5 flex-shrink-0" />
                        @elseif($recommendation['type'] === 'warning')
                            <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-yellow-500 mt-0.5 flex-shrink-0" />
                        @elseif($recommendation['type'] === 'info')
                            <x-heroicon-o-information-circle class="w-5 h-5 text-blue-500 mt-0.5 flex-shrink-0" />
                        @else
                            <x-heroicon-o-check-circle class="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0" />
                        @endif

                        <p class="text-sm
                            @if($recommendation['type'] === 'danger') text-red-700
                            @elseif($recommendation['type'] === 'warning') text-yellow-700
                            @elseif($recommendation['type'] === 'info') text-blue-700
                            @else text-green-700
                            @endif
                        ">
                            {{ $recommendation['message'] }}
                        </p>
                    </div>
                @endforeach
            </div>
        </x-filament::card>
    </div>
</x-filament-panels::page>
