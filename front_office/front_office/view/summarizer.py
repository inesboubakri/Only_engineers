from sumy.parsers.plaintext import PlaintextParser
from sumy.nlp.tokenizers import Tokenizer
from sumy.summarizers.lsa import LsaSummarizer
import sys
import json

# Récupérer le texte via les arguments (passage depuis PHP)
input_text = sys.stdin.read()

# Analyse et résumé
parser = PlaintextParser.from_string(input_text, Tokenizer("french"))
summarizer = LsaSummarizer()
summary = summarizer(parser.document, 3)  # 3 phrases

# Retour JSON
result = ' '.join(str(sentence) for sentence in summary)
print(json.dumps({"summary": result}))
