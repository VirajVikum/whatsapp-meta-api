<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WhatsApp Webhook Test</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <div class="min-h-screen py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-lg shadow-lg p-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">WhatsApp Webhook Testing</h1>
                <p class="text-gray-600 mb-8">Test your WhatsApp webhook connection</p>

                <!-- Configuration Status -->
                <div class="mb-8 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <h2 class="text-lg font-semibold text-blue-900 mb-4">Configuration</h2>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-blue-700">Webhook URL:</span>
                            <code class="text-blue-900 font-mono">/webhook</code>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-blue-700">Verify Token:</span>
                            <code class="text-blue-900 font-mono">{{ substr(config('whatsapp.verify_token'), 0, 10) }}***</code>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-blue-700">Phone ID:</span>
                            <code class="text-blue-900 font-mono">{{ config('whatsapp.phone_id') }}</code>
                        </div>
                    </div>
                </div>

                <!-- Test Buttons -->
                <div class="space-y-4 mb-8">
                    <button 
                        id="testVerifyBtn"
                        class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-lg transition duration-200"
                    >
                        <span class="flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            Test Webhook Verification
                        </span>
                    </button>
                    <button 
                        id="testPayloadBtn"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg transition duration-200"
                    >
                        <span class="flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 10 10.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                            </svg>
                            Send Test Webhook Payload
                        </span>
                    </button>
                    <button 
                        id="clearResultsBtn"
                        class="w-full bg-gray-400 hover:bg-gray-500 text-white font-bold py-3 px-4 rounded-lg transition duration-200"
                    >
                        <span class="flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            Clear Results
                        </span>
                    </button>
                </div>

                <!-- Results Section -->
                <div id="resultsSection" class="hidden">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Test Results</h2>
                    <div id="resultContent" class="bg-gray-50 border border-gray-300 rounded-lg p-4 font-mono text-sm overflow-x-auto">
                        <!-- Results will be inserted here -->
                    </div>
                </div>

                <!-- Instructions -->
                <div class="mt-8 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <h3 class="font-semibold text-yellow-900 mb-2">Instructions:</h3>
                    <ol class="list-decimal list-inside space-y-2 text-sm text-yellow-800">
                        <li>Click "Test Webhook Verification" to verify your webhook endpoint is reachable</li>
                        <li>Click "Send Test Webhook Payload" to simulate receiving a message from WhatsApp</li>
                        <li>Check your application logs to see if the webhook was processed</li>
                        <li>The test message will be stored in the <code class="bg-yellow-100 px-2 py-1 rounded">wa_messages</code> table</li>
                    </ol>
                </div>

                <!-- Debug Info -->
                <div class="mt-8 p-4 bg-gray-100 border border-gray-300 rounded-lg">
                    <h3 class="font-semibold text-gray-900 mb-2">Debug Information:</h3>
                    <div id="debugInfo" class="text-xs space-y-1 text-gray-700">
                        <div>No tests run yet</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('testVerifyBtn').addEventListener('click', testVerification);
        document.getElementById('testPayloadBtn').addEventListener('click', sendTestPayload);
        document.getElementById('clearResultsBtn').addEventListener('click', clearResults);

        function showResults(title, data) {
            const section = document.getElementById('resultsSection');
            const content = document.getElementById('resultContent');
            const debugInfo = document.getElementById('debugInfo');

            const html = `
                <div class="mb-4">
                    <h3 class="font-bold text-gray-900 mb-2">${title}</h3>
                    <pre class="whitespace-pre-wrap break-words text-xs">${JSON.stringify(data, null, 2)}</pre>
                </div>
            `;

            content.innerHTML = html + content.innerHTML;
            section.classList.remove('hidden');

            debugInfo.innerHTML = `
                <div>Timestamp: ${new Date().toISOString()}</div>
                <div>Status: ${data.status || 'Unknown'}</div>
            `;
        }

        function clearResults() {
            document.getElementById('resultsSection').classList.add('hidden');
            document.getElementById('resultContent').innerHTML = '';
            document.getElementById('debugInfo').innerHTML = '<div>No tests run yet</div>';
        }

        async function testVerification() {
            const btn = document.getElementById('testVerifyBtn');
            btn.disabled = true;
            btn.innerHTML = '<span class="flex items-center justify-center gap-2"><svg class="w-5 h-5 animate-spin" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 11-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg> Testing...</span>';

            try {
                const verifyToken = '{{ config("whatsapp.verify_token") }}';
                const challenge = 'test_challenge_' + Date.now();
                const url = '/webhook?hub_mode=subscribe&hub_verify_token=' + encodeURIComponent(verifyToken) + '&hub_challenge=' + encodeURIComponent(challenge);
                
                const response = await fetch(url);
                const text = await response.text();
                
                if (response.status === 200 && text === challenge) {
                    showResults('✅ Webhook Verification Test', {
                        status: 'success',
                        message: 'Webhook verification endpoint is working correctly!',
                        webhook_url: '/webhook',
                        verify_token_set: true,
                        response_status: response.status,
                        challenge_verified: text === challenge
                    });
                } else {
                    showResults('❌ Webhook Verification Test', {
                        status: 'error',
                        message: 'Unexpected response from webhook endpoint',
                        response_status: response.status,
                        response_text: text
                    });
                }
            } catch (error) {
                showResults('❌ Webhook Verification Test', {
                    status: 'error',
                    message: error.message,
                    hint: 'Make sure your app is running and accessible at http://localhost:8000'
                });
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<span class="flex items-center justify-center gap-2"><svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg> Test Webhook Verification</span>';
            }
        }

        async function sendTestPayload() {
            const btn = document.getElementById('testPayloadBtn');
            btn.disabled = true;
            btn.innerHTML = '<span class="flex items-center justify-center gap-2"><svg class="w-5 h-5 animate-spin" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 11-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg> Sending...</span>';

            try {
                const response = await fetch('{{ route("whatsapp.webhook.send.test") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    }
                });
                const data = await response.json();
                
                showResults('✅ Test Webhook Payload Sent', data);
                
                if (data.status === 'success') {
                    alert('Test webhook sent! Check your application logs and the wa_messages table.');
                }
            } catch (error) {
                showResults('❌ Test Webhook Payload Error', {
                    status: 'error',
                    message: error.message
                });
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<span class="flex items-center justify-center gap-2"><svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 10 10.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg> Send Test Webhook Payload</span>';
            }
        }
    </script>
</body>
</html>
