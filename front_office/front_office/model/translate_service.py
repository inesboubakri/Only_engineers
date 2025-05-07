from flask import Flask, request, jsonify
from flask_cors import CORS
from deep_translator import GoogleTranslator

app = Flask(__name__)
CORS(app)  # Active CORS pour toutes les routes

@app.route('/api/translate', methods=['POST'])
def translate_text():
    data = request.get_json()

    # Validation des champs
    text = data.get("text", "")
    target_lang = data.get("target_lang", "")
    source_lang = data.get("source_lang", "auto")

    if not text or not target_lang:
        return jsonify({"error": "Missing 'text' or 'target_lang'"}), 400

    try:
        translator = GoogleTranslator(source=source_lang, target=target_lang)
        translated = translator.translate(text)
        return jsonify({
            "src": source_lang,
            "dest": target_lang,
            "translatedText": translated
        })
    except Exception as e:
        return jsonify({"error": str(e)}), 500

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000)
