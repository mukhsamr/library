function showModal(selector) {
    const modal = document.querySelector(selector)

    if (!modal) return

    modal.classList.remove('hidden')
    modal.classList.add('block')
    modal.setAttribute('aria-hidden', 'false')
}

function hideModal(modal) {
    if (!modal) return

    modal.classList.add('hidden')
    modal.classList.remove('block')
    modal.setAttribute('aria-hidden', 'true')
}

document.addEventListener('click', (event) => {
    const modalToggle = event.target.closest('[data-bs-toggle="modal"]')

    if (modalToggle) {
        event.preventDefault()
        showModal(modalToggle.getAttribute('data-bs-target'))
        return
    }

    const modalDismiss = event.target.closest('[data-bs-dismiss="modal"]')

    if (modalDismiss) {
        event.preventDefault()
        hideModal(modalDismiss.closest('.modal'))
    }
})
