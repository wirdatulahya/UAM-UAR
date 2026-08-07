@if(session('success') || session('error') || session('warning') || session('info') || $errors->any())
    <div id="globalToastContainer" class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1055;">
        @php
            $toasts = [];
            $truncate = function($msg) {
                $pos = strpos($msg, '!');
                if ($pos !== false) return substr($msg, 0, $pos + 1);
                $pos = strpos($msg, '.');
                if ($pos !== false && $pos < 60) return substr($msg, 0, $pos + 1);
                return strlen($msg) > 60 ? substr($msg, 0, 60) . '…' : $msg;
            };
            if (session('success')) $toasts[] = ['type' => 'success', 'icon' => 'bi-check-circle-fill text-success', 'message' => $truncate(session('success'))];
            if (session('error')) $toasts[] = ['type' => 'danger', 'icon' => 'bi-x-circle-fill text-danger', 'message' => $truncate(session('error'))];
            if (session('warning')) $toasts[] = ['type' => 'warning', 'icon' => 'bi-exclamation-triangle-fill text-warning', 'message' => $truncate(session('warning'))];
            if (session('info')) $toasts[] = ['type' => 'info', 'icon' => 'bi-info-circle-fill text-info', 'message' => $truncate(session('info'))];
            
            // Collect the first validation error if any
            if ($errors->any()) {
                $toasts[] = [
                    'type' => 'danger',
                    'icon' => 'bi-exclamation-circle-fill text-danger',
                    'message' => $truncate($errors->first())
                ];
            }
        @endphp

        @foreach($toasts as $toast)
            <div class="toast global-toast align-items-center text-white bg-dark border-0 rounded-3 shadow-lg mb-2" 
                 role="alert" aria-live="assertive" aria-atomic="true" 
                 style="opacity: 1 !important; border: 1px solid rgba(255,255,255,0.08) !important;">
                <div class="d-flex">
                    <div class="toast-body d-flex align-items-center gap-2 py-2 px-3" style="font-weight: 500; font-size: 0.85rem;">
                        <i class="bi {{ $toast['icon'] }} fs-6 flex-shrink-0"></i>
                        <span>{{ $toast['message'] }}</span>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close" style="font-size: 0.7rem; opacity: 0.7;"></button>
                </div>
            </div>
        @endforeach
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var toastElList = [].slice.call(document.querySelectorAll('#globalToastContainer .global-toast'));
            var toastList = toastElList.map(function (toastEl) {
                return new bootstrap.Toast(toastEl, { delay: 3000 });
            });
            toastList.forEach(function (toast) {
                toast.show();
            });
        });
    </script>
@endif
