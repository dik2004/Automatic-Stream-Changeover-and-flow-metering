// Global variables
let isAutoMode = true;
let currentStream = 'A';
let settings = {
    changeoverThreshold: 100,
    pressureLimit: 10,
    temperatureLimit: 50
};

// DOM Elements
const manualOverrideBtn = document.getElementById('manualOverride');
const autoModeBtn = document.getElementById('autoMode');
const settingsForm = document.getElementById('settingsForm');
const currentStreamElement = document.getElementById('currentStream');
const currentFlowRateElement = document.getElementById('currentFlowRate');
const currentPressureElement = document.getElementById('currentPressure');
const currentTemperatureElement = document.getElementById('currentTemperature');
const streamItems = document.querySelectorAll('.stream-item');

// Event Listeners
document.addEventListener('DOMContentLoaded', () => {
    initializeSystem();
    loadSettings();
    startDataSimulation();
    setupStreamControls();
});

// Initialize system
function initializeSystem() {
    updateModeButtons();
    updateStreamStatus();
    loadStreamData();
}

// Update mode buttons
function updateModeButtons() {
    manualOverrideBtn.classList.toggle('active', !isAutoMode);
    autoModeBtn.classList.toggle('active', isAutoMode);
}

// Setup stream controls
function setupStreamControls() {
    manualOverrideBtn.addEventListener('click', () => {
        isAutoMode = false;
        updateModeButtons();
        showNotification('Manual mode activated', 'warning');
    });

    autoModeBtn.addEventListener('click', () => {
        isAutoMode = true;
        updateModeButtons();
        showNotification('Auto mode activated', 'success');
    });

    // Add click handlers to stream items
    streamItems.forEach(item => {
        item.addEventListener('click', () => {
            if (!isAutoMode) {
                const streamId = item.dataset.stream;
                performManualOverride(streamId);
            }
        });
    });
}

// Perform manual override
async function performManualOverride(streamId) {
    try {
        const response = await fetch('api/stream_control.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: 'manual_override',
                stream_id: streamId
            })
        });

        const data = await response.json();
        
        if (data.success) {
            showNotification(data.message, 'success');
            updateStreamStatus();
        } else {
            showNotification(data.error || 'Manual override failed', 'error');
        }
    } catch (error) {
        showNotification('Error performing manual override', 'error');
        console.error('Error:', error);
    }
}

// Check auto mode conditions
async function checkAutoMode() {
    if (!isAutoMode) return;

    try {
        const response = await fetch('api/stream_control.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: 'auto_mode'
            })
        });

        const data = await response.json();
        
        if (data.success) {
            if (data.from_stream && data.to_stream) {
                showNotification(`Auto changeover: ${data.from_stream} → ${data.to_stream}`, 'info');
                updateStreamStatus();
            }
        } else {
            showNotification(data.error || 'Auto mode check failed', 'error');
        }
    } catch (error) {
        showNotification('Error checking auto mode conditions', 'error');
        console.error('Error:', error);
    }
}

// Load settings from localStorage
function loadSettings() {
    const savedSettings = localStorage.getItem('flowMeterSettings');
    if (savedSettings) {
        settings = JSON.parse(savedSettings);
        updateSettingsForm();
    }
}

// Save settings to localStorage
function saveSettings() {
    settings = {
        changeoverThreshold: parseFloat(document.getElementById('changeoverThreshold').value),
        pressureLimit: parseFloat(document.getElementById('pressureLimit').value),
        temperatureLimit: parseFloat(document.getElementById('temperatureLimit').value)
    };
    
    localStorage.setItem('flowMeterSettings', JSON.stringify(settings));
    showNotification('Settings saved successfully', 'success');
}

// Update settings form
function updateSettingsForm() {
    document.getElementById('changeoverThreshold').value = settings.changeoverThreshold;
    document.getElementById('pressureLimit').value = settings.pressureLimit;
    document.getElementById('temperatureLimit').value = settings.temperatureLimit;
}

// Simulate real-time data
function startDataSimulation() {
    setInterval(() => {
        const streamData = generateStreamData();
        updateDisplay(streamData);
        
        if (isAutoMode) {
            checkAutoMode();
        }
    }, 1000);
}

// Generate simulated stream data
function generateStreamData() {
    return {
        flowRate: Math.random() * 150,
        pressure: Math.random() * 15,
        temperature: Math.random() * 60
    };
}

// Update display with new data
function updateDisplay(data) {
    currentFlowRateElement.querySelector('.value').textContent = data.flowRate.toFixed(2);
    currentPressureElement.querySelector('.value').textContent = data.pressure.toFixed(2);
    currentTemperatureElement.querySelector('.value').textContent = data.temperature.toFixed(2);
    
    // Update stream items
    updateStreamItems(data);
}

// Update stream items display
function updateStreamItems(data) {
    streamItems.forEach(item => {
        const stream = item.dataset.stream;
        const flowRate = stream === currentStream ? data.flowRate : Math.random() * 150;
        const status = stream === currentStream ? 'Active' : 'Standby';
        
        item.querySelector('.value').textContent = flowRate.toFixed(2) + ' m³/h';
        item.querySelector('.status').textContent = status;
        item.querySelector('.status').className = `status ${status.toLowerCase()}`;
    });
}

// Update stream status display
function updateStreamStatus() {
    // Fetch current stream status from server
    fetch('api/stream_data.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const activeStream = data.data.find(stream => stream.status === 'active');
                if (activeStream) {
                    currentStream = activeStream.name.replace('Stream ', '');
                    currentStreamElement.querySelector('.stream-name').textContent = activeStream.name;
                    currentStreamElement.querySelector('.status-indicator').className = 'status-indicator active';
                }
            }
        })
        .catch(error => {
            console.error('Error updating stream status:', error);
            showNotification('Error updating stream status', 'error');
        });
}

// Show notification
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.classList.add('show');
    }, 100);
    
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 3000);
}

// Load stream data from server (simulated)
function loadStreamData() {
    // In a real application, this would fetch data from a server
    const data = generateStreamData();
    updateDisplay(data);
} 