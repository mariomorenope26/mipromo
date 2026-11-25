<script>
window.miPromoLookupDni = async function (rawDni, options = {}) {
    const dni = String(rawDni || '').replace(/\D+/g, '').trim();
    if (!/^\d{8}$/.test(dni)) {
        return {
            ok: false,
            code: 'invalid_dni',
            message: 'Ingresa un DNI válido de exactamente 8 dígitos numéricos.'
        };
    }

    const tokenMeta = document.querySelector('meta[name="csrf-token"]');
    const csrf = tokenMeta ? tokenMeta.getAttribute('content') : '';

    try {
        const res = await fetch(@json(route('dni.lookup')), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ dni })
        });

        const payload = await res.json();
        if (payload && payload.ok && payload.data && typeof options.onSuccess === 'function') {
            options.onSuccess(payload.data, payload);
        }
        return payload;
    } catch (e) {
        return {
            ok: false,
            code: 'network_error',
            message: 'No se pudo consultar apiperu.dev. Puedes escribir el nombre manualmente.'
        };
    }
};
</script>
