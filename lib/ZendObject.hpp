#pragma once

extern "C" {
	#include "php.h"
	//#include "ext/standard/info.h"
}

template <typename T>
struct ZendObject {
    static zend_object_handlers ObjectHandlers;
    static const zend_function_entry* FunctionEntry;
    static zend_class_entry* ClassEntry;

    T data;
    zend_object std;

    static zend_object* Create(zend_class_entry *ce) {
        ZendObject *instance = (ZendObject*) ecalloc(1, sizeof(ZendObject));
        zend_object_std_init(&instance->std, ce);
        object_properties_init(&instance->std, ce);
        instance->std.handlers = &ObjectHandlers;
        return &instance->std;
    }

    static zend_object* CreateNew() {
        return Create(ClassEntry);
    }

    static ZendObject* GetZendObject(zend_object *o) {
        return ((ZendObject*) (((char*)o) - XtOffsetOf(ZendObject, std)));
    }

    static ZendObject* GetZendObject(zval *o) {
        auto val = Z_OBJ_P(o);
        return GetZendObject(val);
    }

    static void Free(zend_object *o) {
        ZendObject *instance = GetZendObject(o);
        zend_object_std_dtor(o);
    }

    static void Destroy(zend_object *object) {
        zend_objects_destroy_object(object);
    }

    // Disable dynamic properties
    static void noProperties() {
        zend_throw_exception_ex(NULL, 0, "Properties are not allowed");
    }

    static zval* PropertyRead(zend_object *object, zend_string *member, int type, void **cache_slot, zval *rv) {
        noProperties();
        return &EG(uninitialized_zval);
    }

    static zval* PropertyWrite(zend_object *object, zend_string *member, zval *value, void **cache_slot) {
        noProperties();
        return &EG(uninitialized_zval);
    }

    static int PropertyExists(zend_object *object, zend_string *member, int has_set_exists, void **cache_slot) {
        noProperties();
        return 0;
    }

    static void PropertyUnset(zend_object *object, zend_string *member, void **cache_slot) {
        noProperties();
    }

    static void Dump() {

    }

    static void Register(const char *name) {
        zend_class_entry ce;

        INIT_CLASS_ENTRY_EX(ce, name, strlen(name), FunctionEntry);
        ClassEntry = zend_register_internal_class(&ce);
        ClassEntry->create_object = Create;
        ClassEntry->ce_flags |= ZEND_ACC_FINAL;

        memcpy(&ObjectHandlers, zend_get_std_object_handlers(), sizeof(ObjectHandlers));

        ObjectHandlers.free_obj = Free;
        ObjectHandlers.dtor_obj = Destroy;

        // vector_handlers.get_gc = IndexedArray::gc;
        // vector_handlers.get_debug_info = IndexedArray::dump;
        // vector_handlers.clone_obj = IndexedArray::clone;
        // vector_handlers.cast_object = IndexedArray::castTo;
        //ObjectHandlers.read_property = ZendObject::PropertyRead;
        //ObjectHandlers.write_property = ZendObject::PropertyWrite;
        //ObjectHandlers.has_property = ZendObject::PropertyExists;
        //ObjectHandlers.unset_property = ZendObject::PropertyUnset;

        ObjectHandlers.get_properties = NULL;
        ObjectHandlers.offset = XtOffsetOf(ZendObject, std);
    }
};