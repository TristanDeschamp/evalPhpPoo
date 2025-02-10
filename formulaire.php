<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evaluation PHP POO</title>
    <!-- Import de la police Google "Luckiest Guy" -->
    <link href="https://fonts.googleapis.com/css2?family=Luckiest+Guy&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="mumuse/style.css">
</head>
<body>
    <?php
        require_once 'class_formgenerator.php';

        $form = new FormGenerator($_SERVER['PHP_SELF'], "POST");

        $form->addField("name", "text", "Met ton nom ma couille", [
            'required' => true,
            'class' => 'input-text',
            'id' => 'name-field'
        ]);

        $form->addField("email", "email", "Donne ton email ou je vole ton gouter 🥰", [
            'required' => true,
            'class' => 'input-email',
            'id' => 'email-field'
        ]);

        $form->addField("message", "textarea", "Pourquoi tu me casse les couill... Je veux dire, pourquoi tu me contacte ? 🤗", [
            'required' => true,
            'class' => 'textarea-message',
            'id' => 'message-field'
        ]);

        $form->addField("subject", "select", "De quoi veux-tu parler mon enfant ?", [
            'required' => true,
            'options' => [
                "Où est passé Pascal OP ? Ainsi que Tonio, Djo et Wesley ?", 
                "Ma brouette est complétement trempé... Comment faire ?", 
                "Grand Gourou Pascal va t'il faire de la zonzon ? Et va t'il faire tomber la savonnette sous la douche ?", 
                "Poster une vidéo pour Pascal le grand Gourou ?",
                "Comment autoriser les nains à accéder à mes services depuis mon Renault Master Blanc ?",
                "Que t'il arrivé à la santé mentale du dev qui a codé cette merde ?",
                "Comment payer mon loyer à la fin du mois pour les nuls",
                "Où-trouver les légendaires t-shirt de la légende OP ?"
            ],
            'class' => 'select-subject',
            'id' => 'subject-field'
        ]);

        $form->addField("file_upload", "file", "Viens donc poster une petite vidéo pour tonton Pascal, c'est que du bonus", [
            'required' => true,
            'class' => 'input-file',
            'id' => 'file-upload'
        ]);

        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            if ($form->handleSubmission()) {
                echo "<p style='color: green;'>Le formulaire a été soumis avec succès !</p>";
            } else {
                echo "<p style='color: red;'>Veuillez corriger les erreurs ci-dessous.</p>";
            }
        }

        $form->render();
    ?>

    <script src="mumuse/script.js"></script>
</body>
</html>