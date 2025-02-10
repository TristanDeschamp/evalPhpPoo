// Définir le mot secret et une variable pour accumuler les touches
const secretCode = "docword";
let inputBuffer = "";

// Écouter les événements de frappe sur le document
document.addEventListener("keydown", function(event) {
	// Ajouter la touche en minuscule à notre tampon
	inputBuffer += event.key.toLowerCase();

	// Si le tampon devient plus long que le mot secret, je conserve seulement la fin
	if (inputBuffer.length > secretCode.length) {
	inputBuffer = inputBuffer.slice(-secretCode.length);
	}

	// Si le tampon correspond exactement au mot secret, rediriger vers l'URL
	if (inputBuffer === secretCode) {
	window.location.href = "https://www.francetvinfo.fr/societe/pornographie/video-des-scenes-ou-le-non-consentement-est-explicite-french-bukkake-l-affaire-qui-secoue-le-milieu-du-porno-francais_5385490.html";
	}
});