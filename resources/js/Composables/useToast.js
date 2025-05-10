// Simple toast notification system
export function useToast() {
  const showToast = (message, type = 'success', duration = 3000) => {
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `fixed z-50 top-4 right-4 px-4 py-2 rounded-lg shadow-lg text-white max-w-sm transition-all transform translate-y-0 opacity-100 ${getToastColorClass(type)}`;
    toast.style.zIndex = '9999';
    
    // Create message content
    const content = document.createElement('div');
    content.className = 'flex items-center';
    
    // Add icon
    const icon = document.createElement('span');
    icon.className = 'mr-2';
    icon.innerHTML = getToastIcon(type);
    content.appendChild(icon);
    
    // Add message text
    const text = document.createElement('span');
    text.textContent = message;
    content.appendChild(text);
    
    toast.appendChild(content);
    document.body.appendChild(toast);
    
    // Animate in
    setTimeout(() => {
      toast.classList.add('opacity-100');
      toast.classList.remove('opacity-0');
    }, 10);
    
    // Remove after duration
    setTimeout(() => {
      toast.classList.add('opacity-0');
      toast.classList.remove('opacity-100');
      toast.classList.add('-translate-y-2');
      
      // Remove from DOM after animation
      setTimeout(() => {
        document.body.removeChild(toast);
      }, 300);
    }, duration);
  };
  
  // Helper functions
  const getToastColorClass = (type) => {
    switch (type) {
      case 'success': return 'bg-green-600';
      case 'error': return 'bg-red-600';
      case 'warning': return 'bg-yellow-600';
      case 'info': return 'bg-blue-600';
      default: return 'bg-gray-800';
    }
  };
  
  const getToastIcon = (type) => {
    switch (type) {
      case 'success': return '<i class="fas fa-check-circle"></i>';
      case 'error': return '<i class="fas fa-exclamation-circle"></i>';
      case 'warning': return '<i class="fas fa-exclamation-triangle"></i>';
      case 'info': return '<i class="fas fa-info-circle"></i>';
      default: return '';
    }
  };
  
  // Return public methods
  return {
    success: (message, duration) => showToast(message, 'success', duration),
    error: (message, duration) => showToast(message, 'error', duration),
    warning: (message, duration) => showToast(message, 'warning', duration),
    info: (message, duration) => showToast(message, 'info', duration),
  };
} 