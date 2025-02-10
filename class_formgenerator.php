<?php
require_once 'interface_form.php';

/**
 * Classe FormGenerator
 * 
 * Cette classe permet de générer dynamiquement un formulaire HTML et de gérer la validation 
 * des données soumises. Elle implémente l'interface FormGeneratorInterface et définit 
 * les méthodes obligatoires pour ajouter des champs, afficher le formulaire, traiter les 
 * soumissions et récupérer les erreurs.
 */
class FormGenerator implements FormGeneratorInterface {

	// Propriétés de la classe

	// URL vers laquelle le formulaire sera soumis.
	private string $action;
	// Méthode HTTP utilisée (GET ou POST). La méthode est toujours en majuscules.
	private string $method;
	// Tableau contenant la définition des champs du formulaire (nom, type, label, attributs, etc.).
	private array $fields = [];
	// Tableau statique qui stocke les erreurs rencontrées lors de la validation (clé = nom du champ).
	private static array $errors = [];

	/**
     * Constructeur de la classe.
     *
     * @param string $action URL d'action du formulaire.
     * @param string $method Méthode HTTP (POST ou GET).
     */
	public function __construct(string $action, string $method) {
		$this->action = $action;
		// Conversion de la méthode en majuscules pour uniformiser
		$this->method = strtoupper($method);
	}

	/**
     * Getter pour l'attribut action.
     *
     * @return string
     */
	public function getAction(): string {
		return $this->action;
	}

	/**
     * Setter pour l'attribut action.
     *
     * @param string $action
     */
	public function setAction(string $action): void {
		$this->action = $action;
	}

	/**
     * Getter pour la méthode HTTP.
     *
     * @return string
     */
	public function getMethod(): string {
		return $this->method;
	}

	/**
     * Setter pour la méthode HTTP.
     *
     * @param string $method
     */
	public function setMethod(string $method): void {
		$this->method = strtoupper($method);
	}

	/**
     * Ajoute un champ au formulaire.
     *
     * @param string $name Nom du champ.
     * @param string $type Type du champ (ex: text, email, textarea, select, file).
     * @param string $label Libellé du champ.
     * @param array $attributes Tableau d'attributs HTML supplémentaires (ex: class, id, required, disabled, options).
     */
	public function addField(string $name, string $type, string $label, array $attributes = []): void {
		$this->fields[] = [
			'name'       => $name,
			'type'       => $type,
			'label'      => $label,
			'attributes' => $attributes
		];
	}

	/**
     * Génère et affiche le formulaire en HTML.
     *
     * La méthode parcourt le tableau des champs, crée la structure HTML correspondante et 
     * ajoute les attributs, le pré-remplissage et les éventuels messages d'erreur pour chaque champ.
     */
	public function render(): void {
		// Vérifie si le formulaire contient un champ de type "file" afin d'ajouter l'attribut enctype nécessaire
		$hasFile = false;
		foreach ($this->fields as $field) {
			if ($field['type'] === 'file') {
					$hasFile = true;
					break;
			}
		}

		// Affichage de la balise <form> avec l'attribut action, la méthode et, le cas échéant, enctype pour les fichiers
		echo '<form action="' . strip_tags($this->action) . '" method="' . $this->method . '" ' . ($hasFile ? 'enctype="multipart/form-data"' : '') . '>';

		// Parcours de chaque champ défini pour générer son HTML
		foreach ($this->fields as $field) {
			$name       = $field['name'];
			$type       = $field['type'];
			$label      = $field['label'];
			$attributes = $field['attributes'];

			// Pré-remplissage des champs autres que les fichiers en cas de soumission antérieure
			$value = '';
			if ($type !== 'file') {
					if ($this->method === 'POST' && isset($_POST[$name])) {
						$value = $_POST[$name];
					} elseif ($this->method === 'GET' && isset($_GET[$name])) {
						$value = $_GET[$name];
					}
			}

			// Création d'un conteneur pour le champ (avec une marge en bas)
			echo '<div class="form-group" style="margin-bottom:1em;">';
			
			// Détermination de l'attribut "id" : soit défini dans les attributs, soit égal au nom du champ
			$id = isset($attributes['id']) ? strip_tags($attributes['id']) : strip_tags($name);

			// Affichage du label du champ
			echo '<label for="' . $id . '">' . strip_tags($label) . '</label>';

			// Construction de la chaîne d'attributs HTML à partir du tableau $attributes
         // L'attribut "options" est réservé aux champs select et est ignoré ici
			$attrString = '';
			foreach ($attributes as $attrKey => $attrVal) {
					if ($attrKey === 'options') {
						continue; // Je ne traite pas 'options' comme un attribut HTML
					}
					// Pour les attributs booléens (ex: required, disabled), s'ils sont vrais, j'affiche simplement l'attribut
					if (is_bool($attrVal)) {
						if ($attrVal === true) {
							$attrString .= ' ' . strip_tags($attrKey);
						}
					} else {
						$attrString .= ' ' . strip_tags($attrKey) . '="' . strip_tags($attrVal) . '"';
					}
			}

			// Affichage du champ en fonction de son type avec un switch-case
			switch ($type) {
				case 'text':
				case 'email':
				case 'file':
					// Pour les types text, email et file, on utilise une balise <input>
					echo '<input type="' . strip_tags($type) . '" name="' . strip_tags($name) . '" id="' . $id . '" value="' . strip_tags($value) . '" ' . $attrString . '>';
					break;
				case 'textarea':
					// Pour un champ multi-lignes, utilisation de la balise <textarea>
					echo '<textarea name="' . strip_tags($name) . '" id="' . $id . '" ' . $attrString . '>' . strip_tags($value) . '</textarea>';
					break;
				case 'select':
					// Pour un menu déroulant, utilisation de la balise <select>
					echo '<select name="' . strip_tags($name) . '" id="' . $id . '" ' . $attrString . '>';
					if (isset($attributes['options']) && is_array($attributes['options'])) {
						// Parcours des options définies dans l'attribut "options"
						foreach ($attributes['options'] as $option) {
							// Si la valeur soumise correspond à l'option, je marque celle-ci comme sélectionnée
							$selected = ($option == $value) ? 'selected' : '';
							echo '<option value="' . strip_tags($option) . '" ' . $selected . '>' . strip_tags($option) . '</option>';
						}
					}
				echo '</select>';
				break;
				case 'radio':
					if (isset($attributes['options']) && is_array($attributes['options'])) {
						foreach ($attributes['options'] as $optionValue => $optionLabel) {
							// Vérifier si cette option doit être pré-sélectionnée
							$checked = ($optionValue == $value) ? 'checked' : '';
							// Chaque radio peut avoir un id unique
							echo '<div>';
							echo '<input type="radio" name="' . strip_tags($name) . '" id="' . strip_tags($id . '_' . $optionValue) . '" value="' . strip_tags($optionValue) . '" ' . $checked . ' ' . $attrString . '>';
							echo '<label for="' . strip_tags($id . '_' . $optionValue) . '">' . strip_tags($optionLabel) . '</label>';
							echo '</div>';
						}
					}
				break;

				case 'checkbox':
					// S'il y a plusieurs options, j'attend un tableau de valeurs
					if (isset($attributes['options']) && is_array($attributes['options'])) {
						// Assurer que la valeur récupérée est un tableau
						if (!is_array($value)) {
							$value = [$value];
						}
						foreach ($attributes['options'] as $optionValue => $optionLabel) {
							$checked = in_array($optionValue, $value) ? 'checked' : '';
							echo '<div>';
							// On ajoute [] à l'attribut name pour récupérer un tableau en cas de sélection multiple
							echo '<input type="checkbox" name="' . strip_tags($name) . '[]" id="' . strip_tags($id . '_' . $optionValue) . '" value="' . strip_tags($optionValue) . '" ' . $checked . ' ' . $attrString . '>';
							echo '<label for="' . strip_tags($id . '_' . $optionValue) . '">' . strip_tags($optionLabel) . '</label>';
							echo '</div>';
						}
					} else {
						// Sinon, c'est une checkbox unique
						$checked = ($value) ? 'checked' : '';
						echo '<input type="checkbox" name="' . strip_tags($name) . '" id="' . strip_tags($id) . '" value="1" ' . $checked . ' ' . $attrString . '>';
					}
				break;
			
				default:
					// Par défaut, on affiche un champ de type texte
					echo '<input type="text" name="' . strip_tags($name) . '" id="' . $id . '" value="' . strip_tags($value) . '" ' . $attrString . '>';
			}

			// Affichage éventuel d'un message d'erreur sous le champ (en rouge)
			if (isset(self::$errors[$name])) {
				echo '<div class="error" style="color:red;">' . strip_tags(self::$errors[$name]) . '</div>';
			}
			echo '</div>'; // Fin du conteneur du champ
		}
		// Bouton de soumission du formulaire
		echo '<button type="submit">Envoyer</button>';
		echo '</form>'; // Fin du formulaire
	}

	/**
     * Traite la soumission du formulaire et valide les données.
     *
     * Cette méthode vérifie chaque champ défini, en s'assurant notamment que :
     * - Les champs requis sont remplis.
     * - Le format des emails est correct.
     * - Le texte des zones de saisie (textarea) contient au moins 10 caractères.
     * - Pour les fichiers, le type et la taille sont conformes, et le fichier est bien uploadé.
     *
     * @return bool Retourne true si aucune erreur n'a été détectée, false sinon.
     */
	public function handleSubmission(): bool {
		// Réinitialisation du tableau des erreurs pour chaque soumission
		self::$errors = [];

		// Récupération des données soumises selon la méthode du formulaire (POST ou GET)
		$data = ($this->method === 'POST') ? $_POST : $_GET;

		 // Parcours de chaque champ défini pour valider les données
		foreach ($this->fields as $field) {
			$name       = $field['name'];
			$type       = $field['type'];
			$attributes = $field['attributes'];

			// Si le champ est désactivé (disabled), on n'effectue pas de validation
			if (isset($attributes['disabled']) && $attributes['disabled'] === true) {
				continue;
			}

			// Validation pour les champs autres que les fichiers
			if ($type !== 'file') {
				// Récupération de la valeur soumise (en supprimant les espaces superflus)
				$value = isset($data[$name]) ? trim($data[$name]) : '';

				// Vérification que le champ requis n'est pas vide
				if (isset($attributes['required']) && $attributes['required'] && empty($value)) {
					self::$errors[$name] = 'Ce champ est obligatoire.';
					continue;
				}

				// Si le champ est de type email, validation du format de l'adresse email
				if ($type === 'email' && !empty($value)) {
					if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
						self::$errors[$name] = 'Veuillez entrer un email valide.';
					}
				}

				// Pour une zone de texte, vérification que le message contient au moins 10 caractères
				if ($type === 'textarea' && !empty($value)) {
					if (strlen($value) < 10) {
						self::$errors[$name] = 'Le message doit contenir au moins 10 caractères.';
					}
				}
			} else {
				// Validation spécifique pour les champs de type fichier

				// Vérifie si un fichier a été envoyé pour ce champ
				if (isset($_FILES[$name])) {
					$file = $_FILES[$name];

					// Si le champ est requis et qu'aucun fichier n'a été uploadé correctement, enregistre une erreur
					if ((isset($attributes['required']) && $attributes['required']) && !is_uploaded_file($file['tmp_name'])) {
						self::$errors[$name] = 'Veuillez fournir un fichier.';
						continue;
					}

					// Si un fichier a bien été uploadé, on procède aux vérifications
					if (is_uploaded_file($file['tmp_name'])) {
						// Vérification de la taille maximale du fichier (2 Mo)
						if ($file['size'] > 2097152) { // 2 Mo en octets
							self::$errors[$name] = 'La taille du fichier dépasse 2 Mo.';
							continue;
						}

						 // Récupération de l'extension du fichier et conversion en minuscule
						$extension = strtolower(strchr($file['name'], '.'));
						$allowedExtensions = ['.jpeg', '.png', '.pdf'];
						// Vérification que l'extension est parmi celles autorisées
						if (!in_array($extension, $allowedExtensions)) {
							self::$errors[$name] = 'Type de fichier non autorisé. Seuls JPEG, PNG et PDF sont autorisés.';
							continue;
						}
						// Préparation du dossier de destination ("uploads") pour enregistrer le fichier
						$destinationDir = __DIR__ . '/uploads';
						// Création du dossier si celui-ci n'existe pas
						if (!is_dir($destinationDir)) {
							mkdir($destinationDir, 0777, true);
						}
						// Définition du chemin complet de destination du fichier
						$destination = $destinationDir . '/' . basename($file['name']);
						// Déplacement du fichier depuis le répertoire temporaire vers le dossier "uploads"
						if (!move_uploaded_file($file['tmp_name'], $destination)) {
							self::$errors[$name] = 'Erreur lors du téléchargement du fichier.';
						}
					} else {
						// Si aucun fichier n'est uploadé alors que le champ est requis, enregistre une erreur
						if (isset($attributes['required']) && $attributes['required']) {
							self::$errors[$name] = 'Veuillez fournir un fichier.';
						}
					}
				} else {
					// Si $_FILES ne contient aucune information pour ce champ requis, enregistre une erreur
					if (isset($attributes['required']) && $attributes['required']) {
						self::$errors[$name] = 'Veuillez fournir un fichier.';
					}
				}
			}
		}

		// Retourne true si aucune erreur n'a été détectée, sinon false
		return count(self::$errors) === 0;
	}

	/**
     * Retourne le tableau des erreurs rencontrées lors de la validation.
     *
     * @return array Tableau associatif des erreurs, indexé par le nom du champ.
     */
	public static function getErrors(): array {
		return self::$errors;
	}
}
?>