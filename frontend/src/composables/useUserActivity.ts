import { ref, onMounted, onUnmounted } from 'vue'
import axios from 'axios'
import { useAuthStore } from '../stores/useAuthStore'

/**
 * Composable pour gérer l'activité utilisateur
 * Met é jour automatiquement le lastActive toutes les 30 secondes
 */
export function useUserActivity() {
  const auth = useAuthStore()
  const isActive = ref(true)
  let activityInterval: NodeJS.Timeout | null = null
  let lastActivityUpdate = 0

  // Met é jour l'activité sur le serveur
  const updateActivity = async () => {
    if (!auth.isAuthenticated || !auth.token) {
      return
    }

    // Throttling: ne pas faire plus d'une requéte toutes les 30 secondes
    const now = Date.now()
    if (now - lastActivityUpdate < 30000) {
      return
    }

    try {
      const headers = { Authorization: `Bearer ${auth.token}` }
      await axios.post('http://localhost:8000/api/users/activity', {}, { headers })
      lastActivityUpdate = now
      console.log('? User activity updated')
    } catch (error) {
      console.error('? Failed to update user activity:', error)
    }
  }

  // Démarre le suivi d'activité
  const startTracking = () => {
    if (!auth.isAuthenticated) return

    // Met é jour immédiatement
    updateActivity()

    // Puis toutes les 2 minutes
    activityInterval = setInterval(() => {
      if (auth.isAuthenticated && isActive.value) {
        updateActivity()
      }
    }, 120000) // 2 minutes
  }

  // Arréte le suivi d'activité
  const stopTracking = () => {
    if (activityInterval) {
      clearInterval(activityInterval)
      activityInterval = null
    }
  }

  // Gestionnaires d'événements pour détecter l'activité
  const handleActivity = () => {
    isActive.value = true
    // Mise é jour immédiate si plus de 30 secondes depuis la derniére
    const now = Date.now()
    if (now - lastActivityUpdate > 30000) {
      updateActivity()
    }
  }

  // Détecte quand l'utilisateur devient inactif
  const handleInactive = () => {
    isActive.value = false
  }

  onMounted(() => {
    if (auth.isAuthenticated) {
      startTracking()

      // écoute les événements d'activité
      const events = ['mousedown', 'keydown', 'scroll', 'touchstart', 'click']
      events.forEach(event => {
        document.addEventListener(event, handleActivity, true)
      })

      // écoute l'inactivité (onglet en arriére-plan)
      document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
          handleInactive()
        } else {
          handleActivity()
        }
      })
    }
  })

  onUnmounted(() => {
    stopTracking()

    // Supprime les écouteurs d'événements
    const events = ['mousedown', 'keydown', 'scroll', 'touchstart', 'click']
    events.forEach(event => {
      document.removeEventListener(event, handleActivity, true)
    })
    document.removeEventListener('visibilitychange', handleInactive)
  })

  return {
    isActive,
    updateActivity,
    startTracking,
    stopTracking
  }
}
