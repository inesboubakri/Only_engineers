import sys
from googletrans import Translator

def traduire_texte(texte, langue_source, langue_destination):
    # Initialiser le traducteur
    translator = Translator()

    # Traduire le texte
    traduction = translator.translate(texte, src=langue_source, dest=langue_destination)

    return traduction.text

if __name__ == "__main__":
    # Récupérer les arguments passés depuis PHP
    texte = sys.argv[1]
    langue_source = sys.argv[2]
    langue_destination = sys.argv[3]

    # Effectuer la traduction
    texte_traduit = traduire_texte(texte, langue_source, langue_destination)

    # Afficher le texte traduit
    print(texte_traduit)
