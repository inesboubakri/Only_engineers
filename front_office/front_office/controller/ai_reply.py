# tokenizer.py
import sys
import json
from nltk.tokenize import word_tokenize

# Récupérer le texte passé en argument
texte = sys.argv[1]

# Tokenisation
tokens = word_tokenize(texte)

# Affichage des résultats sous forme JSON
print(json.dumps(tokens))
