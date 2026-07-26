// =========================================================
// 1. PARALLAX en el hero
// =========================================================
(function () { //----------------------------------------------------------------------- Se crea una función anónima y la ejecuta inmediatamente (IIFE) para evitar contaminar el scope global.
    const capas = document.querySelectorAll('[data-speed]'); //------------------------- Aquí busca todos los elementos que tengan el atributo data-speed y los guarda en la variable capas.
    if (!capas.length) return; //------------------------------------------------------- Si no hay elementos con data-speed, sale de la función.

    function actualizarParallax() { 
        const scrollY = window.scrollY; //---------------------------------------------- Guarda cuántos píxeles ha bajado el usuario.
        capas.forEach((capa) => { //---------------------------------------------------- Bucle que recorre cada capa y le aplica el efecto parallax.
            const velocidad = parseFloat(capa.dataset.speed) || 0; //------------------- Obtiene la velocidad de la capa desde el atributo data-speed. Si no hay valor, usa 0.
            capa.style.setProperty('--parallax-y', `${scrollY * velocidad}px`); //------ Se actualiza SOLO la variable CSS --parallax-y, nunca el transform entero, para no romper otros transforms (como el centrado) que ya tenga el elemento.
        });
    }

    window.addEventListener('scroll', actualizarParallax, { passive: true }); //-------- Cada vez que haces scroll, llama a actualizarParallax. El { passive: true } es para mejorar el rendimiento y decirle al navegador que no vamos a llamar preventDefault() en este evento.
})();


// =========================================================
// 2. EL ROBOT se mueve y desaparece al hacer scroll
// =========================================================
(function () { //----------------------------------------------------------------------- Se crea otra función anónima y la ejecuta inmediatamente (IIFE) para evitar contaminar el scope global.
    const robotWrap = document.getElementById('robotWrap'); //-------------------------- Busca el contenedor del robot y lo guarda en la variable robotWrap.
    const hero = document.getElementById('hero'); //------------------------------------ Busca el contenedor del hero y lo guarda en la variable hero.
    if (!robotWrap || !hero) return; //------------------------------------------------- Si no encuentra ninguno de los dos, sale de la función.

    const DESPLAZ_MAX = 160; //--------------------------------------------------------- Define la cantidad máxima de desplazamiento del robot en píxeles.
    let ticking = false; //------------------------------------------------------------- Sirve para que no se ejecuten cientos de animaciones al mismo tiempo.

    function actualizar() {
        const heroHeight = hero.offsetHeight; //---------------------------------------- Obtiene la altura del hero para calcular el progreso del scroll.
        const distanciaEfecto = heroHeight * 0.7; //------------------------------------ El efecto termina cuando has recorrido el 70% del hero.  
        const progreso = Math.max(0, Math.min(1, window.scrollY / distanciaEfecto)); //- Hace que el progreso esté siempre entre 0 y 1, aunque el usuario haga scroll más allá del hero.

        const traslado = progreso * DESPLAZ_MAX;
        const opacidad = 1 - progreso;

        robotWrap.style.transform = `translateX(-50%) translateY(${traslado}px)`; //---- Mueve el robot.
        robotWrap.style.opacity = opacidad; //------------------------------------------ Cambia la opacidad del robot y lo hace desaparecer progresivamente.

        ticking = false;
    }

    window.addEventListener('scroll', () => {
        if (!ticking) {
            requestAnimationFrame(actualizar); //--------------------------------------- Llama a actualizar() solo cuando toque dibujar el siguiente frame, para que no se ejecute demasiadas veces por segundo.
            ticking = true;
        }
    }, { passive: true }); //----------------------------------------------------------- El { passive: true } es para mejorar el rendimiento y decirle al navegador que no vamos a llamar preventDefault() en este evento.
})();


// =========================================================
// 3. APARICIÓN PROGRESIVA de las tarjetas al hacer scroll
// =========================================================
(function () { //----------------------------------------------------------------------- Se crea otra función anónima y la ejecuta inmediatamente (IIFE) para evitar contaminar el scope global.
    const tarjetas = document.querySelectorAll('.servicio-card, .plan-card'); //-------- Busca todas las tarjetas de servicios y planes y las guarda en la variable tarjetas.
    if (!tarjetas.length) return; //---------------------------------------------------- Si no hay tarjetas, sale de la función.

    const observador = new IntersectionObserver((entradas) => { //---------------------- Es una API del navegador. Crea un observador que se activa cuando las tarjetas entran en el viewport.
        entradas.forEach((entrada, indice) => { //-------------------------------------- Recorre cada entrada (tarjeta) que ha entrado en el viewport.
            if (entrada.isIntersecting) { //-------------------------------------------- Pregunta ¿Ya está entrando en pantalla?.
                setTimeout(() => { //--------------------------------------------------- Hace que aparezcan las tarjetas una a una con un pequeño retraso para crear un efecto de aparición escalonada.
                    entrada.target.classList.add('visible'); //------------------------- Añade la clase visible a la tarjeta, que activa la animación de aparición. Entonces CSS hace la animación.
                }, indice * 80); //----------------------------------------------------- Cada tarjeta se retrasa 80ms más que la anterior.
                observador.unobserve(entrada.target); //-------------------------------- Deja de observar la tarjeta y así no vuelve a animarla.
            }
        });
    }, { threshold: 0.15 }); //--------------------------------------------------------- threshold: 0.15 significa que la tarjeta se considera visible cuando al menos el 15% de ella está en el viewport.

    tarjetas.forEach((tarjeta) => observador.observe(tarjeta));
})();

