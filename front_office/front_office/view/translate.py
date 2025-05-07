import sys
import json
import requests

def translate_text(text, target_lang, source_lang='auto'):
    try:
        response = requests.post("https://libretranslate.de/translate", data={
            "q": text,
            "source": source_lang,
            "target": target_lang,
            "format": "text"
        })
        result = response.json()

        if 'translatedText' in result:
            return {"status": "success", "translated_text": result["translatedText"]}
        else:
            return {"status": "error", "message": result.get("error", "Unknown error")}
    except Exception as e:
        return {"status": "error", "message": str(e)}

if __name__ == "__main__":
    try:
        input_data = sys.stdin.read()
        if not input_data:
            print(json.dumps({"status": "error", "message": "No input provided"}))
            sys.exit(1)

        data = json.loads(input_data)
        text = data.get("text", "")
        target_lang = data.get("target_lang", "fr")
        source_lang = data.get("source_lang", "auto")

        if not text:
            print(json.dumps({"status": "error", "message": "Text is required"}))
            sys.exit(1)

        result = translate_text(text, target_lang, source_lang)
        print(json.dumps(result))

    except json.JSONDecodeError as e:
        print(json.dumps({"status": "error", "message": "Invalid JSON input: " + str(e)}))
    except Exception as e:
        print(json.dumps({"status": "error", "message": "Unexpected error: " + str(e)}))
