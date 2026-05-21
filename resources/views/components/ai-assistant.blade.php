<div id="ai-assistant-wrapper" class="fixed bottom-6 right-6 z-[9999] flex flex-col items-end">
    
    <!-- Fenêtre de Chat (Masquée par défaut) -->
    <div id="ai-chat-window" class="hidden mb-4 w-80 sm:w-96 bg-white rounded-2xl shadow-2xl border border-secondary/20 overflow-hidden flex-col origin-bottom-right transition-all duration-300 transform scale-95 opacity-0">
        <!-- Header -->
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center">
                    <i data-lucide="bot" class="w-4 h-4 text-white"></i>
                </div>
                <div>
                    <h3 class="text-white text-sm font-semibold">Kuété (Assistant IA)</h3>
                    <p class="text-white/70 text-[10px]">Toujours là pour vous aider</p>
                </div>
            </div>
            <button onclick="toggleAiChat()" class="text-white/70 hover:text-white transition-colors">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <!-- Zone des messages -->
        <div class="flex-1 p-4 h-80 overflow-y-auto bg-gray-50 flex flex-col gap-4">
            
            <!-- Message de l'IA (Placeholder) -->
            <div class="flex items-start gap-2 max-w-[85%]">
                <div class="w-6 h-6 rounded-full bg-indigo-100 flex items-center justify-center flex-shrink-0 mt-1">
                    <i data-lucide="sparkles" class="w-3 h-3 text-indigo-600"></i>
                </div>
                <div class="bg-white border border-gray-100 p-3 rounded-2xl rounded-tl-sm shadow-sm text-xs text-gray-700 leading-relaxed">
                    Bonjour {{ Auth::user()->name ?? '' }} ! 👋 <br>
                    Je suis <strong>Kuété</strong>, ton assistant virtuel. Je peux t'aider à retrouver une réservation, comprendre un processus, ou analyser les performances de l'hôtel. Que puis-je faire pour toi ?
                </div>
            </div>

            <!-- Exemple de question suggérée (Design only) -->
            <div class="flex flex-wrap gap-2 mt-2">
                <button class="px-3 py-1.5 bg-white border border-indigo-100 rounded-full text-[10px] text-indigo-600 hover:bg-indigo-50 transition-colors">
                    Combien d'arrivées aujourd'hui ?
                </button>
                <button class="px-3 py-1.5 bg-white border border-indigo-100 rounded-full text-[10px] text-indigo-600 hover:bg-indigo-50 transition-colors">
                    Aide moi à faire un check-in
                </button>
            </div>

        </div>

        <!-- Input Area -->
        <div class="p-3 bg-white border-t border-gray-100">
            <div class="relative flex items-center">
                <input type="text" placeholder="Posez votre question..." class="w-full bg-gray-50 border border-gray-200 text-sm rounded-full pl-4 pr-10 py-2.5 focus:outline-none focus:border-indigo-300 focus:ring-1 focus:ring-indigo-300 transition-all">
                <button type="button" class="absolute right-1 w-8 h-8 flex items-center justify-center bg-indigo-600 text-white rounded-full hover:bg-indigo-700 transition-colors">
                    <i data-lucide="send" class="w-3.5 h-3.5 ml-0.5"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Bouton Flottant -->
    <button onclick="toggleAiChat()" id="ai-chat-button" class="group relative flex items-center justify-center w-14 h-14 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 hover:scale-105 transition-all duration-300">
        <!-- Glow effect -->
        <div class="absolute inset-0 rounded-full bg-white opacity-0 group-hover:opacity-20 transition-opacity"></div>
        <i data-lucide="sparkles" class="w-6 h-6 text-white absolute"></i>
        
        <!-- Tooltip -->
        <span class="absolute right-full mr-4 bg-gray-900 text-white text-[10px] font-semibold px-2 py-1 rounded-md opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity whitespace-nowrap">
            Besoin d'aide ?
        </span>
    </button>

</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const chatWindow = document.getElementById('ai-chat-window');
        const isOpen = localStorage.getItem('ai_chat_open') === 'true';
        
        if (isOpen) {
            chatWindow.classList.remove('hidden');
            // Remove the scale/opacity to make it fully visible instantly
            chatWindow.classList.remove('scale-95', 'opacity-0');
            chatWindow.classList.add('scale-100', 'opacity-100');
        }
    });

    function toggleAiChat() {
        const chatWindow = document.getElementById('ai-chat-window');
        
        if (chatWindow.classList.contains('hidden')) {
            // Ouverture
            chatWindow.classList.remove('hidden');
            localStorage.setItem('ai_chat_open', 'true');
            // Request animation frame to ensure the transition triggers
            requestAnimationFrame(() => {
                chatWindow.classList.remove('scale-95', 'opacity-0');
                chatWindow.classList.add('scale-100', 'opacity-100');
            });
        } else {
            // Fermeture
            localStorage.setItem('ai_chat_open', 'false');
            chatWindow.classList.remove('scale-100', 'opacity-100');
            chatWindow.classList.add('scale-95', 'opacity-0');
            
            // Wait for transition to finish before hiding
            setTimeout(() => {
                chatWindow.classList.add('hidden');
            }, 300); // Matches the duration-300 class
        }
    }
</script>
